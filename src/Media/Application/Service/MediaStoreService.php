<?php

declare(strict_types=1);

namespace App\Media\Application\Service;

use App\Media\Domain\Entity\MediaFolder;
use App\Media\Domain\Message\CreateMediaMessenger;
use App\Media\Infrastructure\Repository\MediaFolderRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\UnicodeString;
use App\Media\Domain\Entity\Media;
use App\Media\Application\Util\FileHelperTrait;

use function in_array;


/**
 * @package App\Media\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class MediaStoreService
{
    use FileHelperTrait;

    private const string MIME_IMAGE = 'image/';
    private const string MIME_VIDEO = 'video/';
    private const array MIME_DOCUMENTS = [
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/rtf', 'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet', 'application/x-abiword',
        'application/vnd.ms-excel', 'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/epub+zip', 'application/x-freearc', 'text/plain',
        'text/csv', 'application/json', 'application/xml',
    ];

    public function __construct(
        protected FilesystemOperator $filesystem,
        private readonly RequestStack $requestStack,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        private readonly MediaElasticsearchService $mediaElasticsearchService,
        private readonly MediaFolderRepository $mediaFolderRepository
    ) {
    }

    /**
     * Generates attributes for the media from the uploaded file.
     *
     * @param UploadedFile $file
     * @param string       $userId
     * @param Request      $request
     *
     * @throws ExceptionInterface
     * @throws FilesystemException
     * @return Media
     */
    public function generateAttributeMedia(UploadedFile $file, string $userId, Request $request): Media
    {
        $media = new Media();
        $media->setMediaFolder($this->getMediaFolder($request, $userId));

        $media->setPath(
            $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() . '/medias/' . $this->storeFile($file, $media->getMediaFolder()?->getPath()));

        $this->bus->dispatch(
            new CreateMediaMessenger(
                $media->getId(),
                $media->getMediaFolder()?->getId(),
                $media->getPath(),
                $file->getClientOriginalName(),
                $file->getMimeType(),
                $file->getSize(),
                $file->getType(), $userId
            )
        );
        return $media;
    }

    /**
     * @throws FilesystemException
     */
    private function storeFile(UploadedFile $file, string $path): string
    {
        umask(0022);
        $sanitizedFileName = $this->sanitizeFileName($file->getClientOriginalName());
        $uniqueFileName = $path . uniqid('', true) . '.' . pathinfo($sanitizedFileName, PATHINFO_EXTENSION);

        $stream = fopen($file->getRealPath(), 'rb+');
        if (!$stream) {
            throw new RuntimeException("Impossible d'ouvrir le fichier pour lecture.");
        }
        $this->filesystem->writeStream($uniqueFileName, $stream);
        fclose($stream);
        $this->chmod_recursive('public/medias/', 0777);
        return $uniqueFileName;
    }

    public function initializeMediaAttributes(string $mediaId, string $mediaFolderId , string $path , ?string $fileName, ?string $mimeType, ?int $size, ?string $type, string $userId): void
    {
        $mediaFolder = $this->mediaFolderRepository->find($mediaFolderId);
        $media = new Media();
        $media->setId(Uuid::fromString($mediaId));
        $media->setFileName($fileName);
        $media->setMimeType($mimeType);
        $media->setFileExtension($fileName);
        $media->setFileSize($size);
        $media->setMediaType($this->getFileType($mimeType));
        $media->setContextKey($mediaFolder?->getName());
        $media->setPath($path);
        $media->setMediaFolder($mediaFolder);

        $metadata = [
            'Name' => $fileName,
            'Title' => $fileName,
            'Alt' => $fileName,
            'Type' => $type,
            'MIME-TYPE' => $mimeType,
            'Size' => $media->getFileSize(),
            'Uploaded at' => new DateTime('now'),
        ];
        $camelCasedMetadata = [];
        foreach ($metadata as $key => $value) {
            $unicodeString = new UnicodeString(strtolower(str_replace(['-', ' '], '_', $key)));
            $camelCasedKey = (string) $unicodeString->camel();

            $camelCasedMetadata[$camelCasedKey] = $value;
        }
        $media->setMetaData($camelCasedMetadata);
        $media = $this->populateMedia($media, $userId);
        $this->mediaElasticsearchService->indexMediaInElasticsearch($media);

        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }

    private function getFileType(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, self::MIME_IMAGE) => 'image',
            str_starts_with($mimeType, self::MIME_VIDEO) => 'video',
            in_array($mimeType, self::MIME_DOCUMENTS) => 'document',
            default => 'unknown',
        };
    }

    /**
     * @param Media  $media
     * @param string $userId
     *
     * @return Media
     */
    private function populateMedia(Media $media, string $userId): Media
    {
        $uuidUser = ($userId && Uuid::isValid($userId))
            ? Uuid::fromString($userId)
            : Uuid::uuid1();
        $media->setUserId($uuidUser);
        $media->setWorkplaceId(Uuid::fromString($userId));

        return $media;
    }

    private function validatePath(string $path): void
    {
        $normalizedPath = realpath($path);
        $allowedDirectory = realpath($_ENV['ALLOWED_UPLOAD_DIRECTORY'] ?? '/tmp/');

        if (
            $normalizedPath === false ||
            $allowedDirectory === false ||
            !str_starts_with($normalizedPath, $allowedDirectory)
        ) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                'Invalid file path detected. Access forbidden.'
            );
        }
    }

    /**
     * @param $dir
     * @param $mode
     *
     * @return void
     */
    private function chmod_recursive($dir, $mode): void
    {
        chmod($dir, $mode);
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $this->chmod_recursive($filePath, $mode);
            } else {
                chmod($filePath, $mode);
            }
        }
    }

    /**
     * @param Request $request
     * @param string  $userId
     *
     * @return MediaFolder|null
     */
    private function getMediaFolder(Request $request, string $userId): ?MediaFolder
    {
        $mediaFolderRequest = $request->request->get('mediaFolder');

        $mediaFolder = $this->mediaFolderRepository->findOneBy([
            'workplaceId' => Uuid::fromString($userId),
            'name' => $mediaFolderRequest
        ]);

        if(!$mediaFolder) {
            $mediaFolder = new MediaFolder();
            $mediaFolder->setName($mediaFolderRequest);
            $mediaFolder->setWorkplaceId(Uuid::fromString($userId));
            $mediaFolder->setPath(Uuid::fromString($userId) . '/' . $mediaFolderRequest . '/');
            $this->entityManager->persist($mediaFolder);
            $this->entityManager->flush();
        }

        return $mediaFolder;
    }
}

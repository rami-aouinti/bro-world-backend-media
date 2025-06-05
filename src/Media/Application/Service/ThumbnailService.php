<?php

declare(strict_types=1);

namespace App\Media\Application\Service;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RuntimeException;
use App\Media\Domain\Entity\MediaThumbnail;
use App\Media\Infrastructure\Repository\MediaRepository;
use App\Media\Application\Util\FileHelperTrait;

/**
 * @package App\Media\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class ThumbnailService
{
    use FileHelperTrait;

    public function __construct(
        private MediaRepository $mediaRepository,
        private FilesystemOperator $azureStorage
    ) {
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function generateImageThumbnail(string $path, int $size, $mediaId): void
    {
        if (!$this->azureStorage->fileExists($path)) {
            throw new RuntimeException("File not exist : $path");
        }

        $fileContent = $this->azureStorage->read($path);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'thumb_');
        file_put_contents($tempFilePath, $fileContent);

        $this->ensureAzureDirectoryExists('uploads/images/thumbnails/');

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $validExtensions = ['gif', 'jpeg', 'png', 'wbmp', 'xbm', 'bmp', 'webp', 'avif'];
        if (!in_array(strtolower($extension), $validExtensions)) {
            $extension = 'jpeg';
        }

        $outputPath = 'uploads/images/thumbnails/' . basename(
                $path,
                '.' . pathinfo($path, PATHINFO_EXTENSION)
            ) . "_thumbnail_{$size}.{$extension}";

        $imagine = new Imagine();
        $image = $imagine->open($tempFilePath);
        $thumbnail = $image->thumbnail(new Box($size, $size), ManipulatorInterface::THUMBNAIL_OUTBOUND);

        $thumbnail->save($tempFilePath, ['format' => $extension]);

        $this->azureStorage->write($outputPath, file_get_contents($tempFilePath));

        unlink($tempFilePath);

        $this->createThumbnail($size, $outputPath, $mediaId);
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    public function generateVideoThumbnail(string $videoPath, int $size, $mediaId, int $timeInSeconds = 1): void
    {
        if (!$this->azureStorage->fileExists($videoPath)) {
            throw new RuntimeException("File not exist: $videoPath");
        }

        $fileContent = $this->azureStorage->read($videoPath);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'thumb_');
        file_put_contents($tempFilePath, $fileContent);

        $this->ensureAzureDirectoryExists('uploads/videos/thumbnails/');

        $outputPath = 'uploads/videos/thumbnails/' . basename(
                $videoPath,
                '.' . pathinfo($videoPath, PATHINFO_EXTENSION)
            ) . "_thumbnail_{$size}.png";

        $tempThumbnailPath = tempnam(sys_get_temp_dir(), 'vid_thumb_') . '.png';
        $command = sprintf(
            'ffmpeg -ss %d -i %s -vframes 1 -vf "scale=%d:%d" %s',
            $timeInSeconds,
            escapeshellarg($tempFilePath),
            $size,
            $size,
            escapeshellarg($tempThumbnailPath)
        );

        exec($command . ' 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            unlink($tempFilePath);
            throw new RuntimeException('Error by execute ffmpeg: ' . implode("\n", $output));
        }

        $this->azureStorage->write($outputPath, file_get_contents($tempThumbnailPath));

        unlink($tempFilePath);
        unlink($tempThumbnailPath);

        $this->createThumbnail($size, $outputPath, $mediaId);
    }

    /**
     * @param int    $size
     * @param string $resourcePath
     * @param        $mediaId
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @return void
     */
    private function createThumbnail(int $size, string $resourcePath, $mediaId): void
    {
        $media = $this->mediaRepository->find($mediaId);
        if ($media === null) {
            throw new InvalidArgumentException("Media not found for id: {$mediaId}");
        }
        $thumbnail = new MediaThumbnail();
        $thumbnail->setWidth($size);
        $thumbnail->setHeight($size);
        $thumbnail->setPath($resourcePath);
        $thumbnail->setWorkplaceId($media->getWorkplaceId());
        $media->addThumbnail($thumbnail);
        $this->mediaRepository->save($media);
    }

    /**
     * @throws FilesystemException
     */
    private function ensureAzureDirectoryExists(string $directory): string
    {
        if (!$this->azureStorage->directoryExists($directory)) {
            $this->azureStorage->createDirectory($directory);
        }

        return $directory;
    }
}

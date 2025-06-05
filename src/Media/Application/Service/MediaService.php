<?php

declare(strict_types=1);

namespace App\Media\Application\Service;

use App\Media\Domain\Repository\Interfaces\MediaRepositoryInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Media\Domain\Entity\Media;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

/**
 * @package App\Media\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class MediaService
{
    public function __construct(
        private MediaValidateService $mediaValidateService,
        private MediaStoreService $mediaStoreService,
        private ThumbnailService $mediaThumbnailGenerator,
        private MediaRepositoryInterface $mediaRepository
    ) {
    }

    /**
     * @throws FilesystemException
     * @throws ExceptionInterface
     */
    public function processSingleMedia(UploadedFile $file, string $userId, Request $request): Media
    {
        $this->mediaValidateService->checkMimeTypeAndSize($file);
        return $this->mediaStoreService->generateAttributeMedia($file, $userId, $request);
    }

    /**
     * @param string  $id
     * @param Request $request
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws FilesystemException
     * @return null|bool
     */
    protected function generateThumbnail(string $id, Request $request): ?bool
    {
        $media = $this->mediaRepository->find($id);

        if($media) {
            if ($media->getMediaType() === "video") {
                $this->mediaThumbnailGenerator->generateVideoThumbnail($media->getPath(), 200, $media->getId());
            } else {
                $this->mediaThumbnailGenerator->generateImageThumbnail($media->getPath(), 200, $media->getId());
            }
        }

        return true;
    }
}

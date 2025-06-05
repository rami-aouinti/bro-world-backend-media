<?php

declare(strict_types=1);

namespace App\Media\Domain\Message;

use App\General\Domain\Message\Interfaces\MessageHighInterface;
use App\Media\Domain\Entity\Media;

/**
 * Class CreateMediaMessenger
 *
 * @package App\Media\Domain\Message
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class CreateMediaMessenger implements MessageHighInterface
{
    public function __construct(
        private ?string $mediaId,
        private ?string $mediaFolderId,
        private ?string $path,
        private ?string $fileName,
        private ?string $mimeType,
        private ?int $size,
        private ?string $type,
        private ?string $userId
    )
    {
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function getMediaFolderId(): ?string
    {
        return $this->mediaFolderId;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }
}

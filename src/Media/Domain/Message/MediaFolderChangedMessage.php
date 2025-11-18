<?php

declare(strict_types=1);

namespace App\Media\Domain\Message;

/**
 * @package App\Media\Domain\Message
 */
readonly class MediaFolderChangedMessage
{
    public function __construct(private string $userId)
    {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}

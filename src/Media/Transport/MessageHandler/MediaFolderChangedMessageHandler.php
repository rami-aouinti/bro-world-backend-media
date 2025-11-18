<?php

declare(strict_types=1);

namespace App\Media\Transport\MessageHandler;

use App\Media\Application\Service\MediaFolderCacheService;
use App\Media\Domain\Message\MediaFolderChangedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @package App\Media\Transport\MessageHandler
 */
#[AsMessageHandler]
readonly class MediaFolderChangedMessageHandler
{
    public function __construct(private MediaFolderCacheService $cacheService)
    {
    }

    public function __invoke(MediaFolderChangedMessage $message): void
    {
        $this->cacheService->invalidateUser($message->getUserId());
    }
}

<?php

declare(strict_types=1);

namespace App\Media\Transport\MessageHandler;

use App\Media\Application\Service\MediaStoreService;
use App\Media\Domain\Message\CreateMediaMessenger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class CreateMediaHandlerMessage
 *
 * @package App\Media\Transport\MessageHandler
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsMessageHandler]
readonly class CreateMediaHandlerMessage
{
    public function __construct(
        private MediaStoreService $mediaStoreService
    )
    {
    }

    /**
     * @param CreateMediaMessenger $message
     *
     * @return void
     */
    public function __invoke(CreateMediaMessenger $message): void
    {
        $this->handleMessage($message);
    }

    private function handleMessage(CreateMediaMessenger $message): void
    {
        $this->mediaStoreService->initializeMediaAttributes(
            $message->getMediaId(),
            $message->getMediaFolderId(),
            $message->getPath(),
            $message->getFileName(),
            $message->getMimeType(),
            $message->getSize(),
            $message->getType(),
            $message->getUserId()
        );
    }
}

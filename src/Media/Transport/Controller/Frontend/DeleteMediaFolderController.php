<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use App\Media\Domain\Entity\MediaFolder;
use App\Media\Domain\Message\MediaFolderChangedMessage;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class DeleteMediaFolderController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws ExceptionInterface
     */
    #[Route(
        path: '/v1/platform/mediaFolder/{folder}',
        methods: [Request::METHOD_DELETE],
    )]
    public function __invoke(SymfonyUser $symfonyUser, MediaFolder $folder): JsonResponse
    {
        $this->entityManager->remove($folder);
        $this->entityManager->flush();

        $this->messageBus->dispatch(
            new MediaFolderChangedMessage($symfonyUser->getUserIdentifier())
        );

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                'success',
                'json',
                [
                    'groups' => 'Media',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}

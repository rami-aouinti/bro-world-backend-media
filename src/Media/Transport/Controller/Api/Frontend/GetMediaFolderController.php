<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Api\Frontend;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\Interfaces\MediaRepositoryInterface;
use App\Media\Infrastructure\Repository\MediaFolderRepository;
use Doctrine\ORM\Exception\NotSupported;
use JsonException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class GetMediaFolderController
{
    public function __construct(
        private SerializerInterface $serializer,
        private MediaFolderRepository $repository
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param SymfonyUser $symfonyUser
     * @param string      $folder
     *
     * @throws JsonException
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/mediaFolder/{folder}',
        methods: [Request::METHOD_GET],
    )]
    #[OA\Response(
        response: 200,
        description: 'Media data',
        content: new JsonContent(
            ref: new Model(
                type: Media::class,
                groups: ['Media'],
            ),
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid token (not found or expired)',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 401,
                'message' => 'JWT Token not found',
            ],
        ),
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 403,
                'message' => 'Access denied',
            ],
        ),
    )]
    public function __invoke(SymfonyUser $symfonyUser, string $folder): JsonResponse
    {
        $mediaFolders = $this->repository->findBy([
            'workplaceId' => $symfonyUser->getUserIdentifier(),
            'name' => $folder
        ]);
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $mediaFolders,
                'json',
                [
                    'groups' => 'mediaFolder:read',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}

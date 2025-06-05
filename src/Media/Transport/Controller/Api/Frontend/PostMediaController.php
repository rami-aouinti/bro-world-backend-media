<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Api\Frontend;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Media\Application\Service\MediaService;
use App\Media\Domain\Entity\Media;
use JsonException;
use League\Flysystem\FilesystemException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class PostMediaController
{
    public function __construct(
        private SerializerInterface $serializer,
        private MediaService $mediaService
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param SymfonyUser $symfonyUser
     * @param Request     $request
     *
     * @throws FilesystemException
     * @throws JsonException
     * @throws Throwable
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/media',
        methods: [Request::METHOD_POST],
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
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        $files = $request->files->all();

        $medias = array_map(function ($file) use ($request, $symfonyUser) {
            return $this->mediaService->processSingleMedia(
                $file,
                $symfonyUser->getUserIdentifier(),
                $request
            );
        }, $files["files"]);

        $output = JSON::decode(
            $this->serializer->serialize(
                $medias,
                'json',
                ['groups' => 'Media:create']
            ),
            true
        );

        return new JsonResponse($output);
    }
}

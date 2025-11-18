<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use App\Media\Application\Service\MediaFolderCacheService;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class GetMediaFolderController
{
    public function __construct(private MediaFolderCacheService $mediaFolderCacheService)
    {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param SymfonyUser $symfonyUser
     * @param string      $folder
     *
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/mediaFolder/{folder}',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $symfonyUser, string $folder): JsonResponse
    {
        $folders = $this->mediaFolderCacheService->getFolderByName(
            $symfonyUser->getUserIdentifier(),
            $folder
        );

        return new JsonResponse($folders);
    }
}

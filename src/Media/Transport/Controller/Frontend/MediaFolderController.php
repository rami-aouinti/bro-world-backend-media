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
readonly class MediaFolderController
{
    public function __construct(private MediaFolderCacheService $mediaFolderCacheService)
    {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param SymfonyUser $symfonyUser
     *
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/mediaFolder',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $symfonyUser): JsonResponse
    {
        $folders = $this->mediaFolderCacheService->getRootFolders($symfonyUser->getUserIdentifier());

        return new JsonResponse($folders);
    }
}

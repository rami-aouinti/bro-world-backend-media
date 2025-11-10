<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
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
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        $files = $request->files->all();

        $medias = array_map(function ($file) use ($request, $symfonyUser) {
            return $this->mediaService->processSingleMedia(
                $file,
                $symfonyUser->getId(),
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

<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class MediaFolderController
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
     *
     * @throws JsonException
     * @throws ExceptionInterface
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/mediaFolder',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $symfonyUser): JsonResponse
    {
        $mediaFolders = $this->repository->findBy([
            'workplaceId' => $symfonyUser->getUserIdentifier(),
            'parent' => null
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

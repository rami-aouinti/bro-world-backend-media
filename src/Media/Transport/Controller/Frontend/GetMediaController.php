<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\Interfaces\MediaRepositoryInterface;
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
readonly class GetMediaController
{
    public function __construct(
        private SerializerInterface $serializer
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param SymfonyUser $symfonyUser
     * @param Media       $media
     *
     * @throws JsonException
     * @throws ExceptionInterface
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/media/{media}',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $symfonyUser, Media $media): JsonResponse
    {
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $media,
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

<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use App\General\Domain\Utils\JSON;
use App\Media\Domain\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
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
readonly class UpdateMediaController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/platform/media/{media}',
        methods: [Request::METHOD_PUT],
    )]
    public function __invoke(Media $media, Request $request): JsonResponse
    {
        $payload = $request->request->all();

        if ($payload === [] && $request->getContent() !== '') {
            /** @var array<string, mixed> $payload */
            $payload = JSON::decode($request->getContent(), true);
        }

        if (array_key_exists('userId', $payload)) {
            $userId = $payload['userId'];
            $uuidUser = ($userId && is_string($userId) && Uuid::isValid($userId))
                ? Uuid::fromString($userId)
                : Uuid::uuid1();
            $media->setUserId($uuidUser);
        }

        if (array_key_exists('title', $payload)) {
            $media->setTitle($payload['title']);
        }

        if (array_key_exists('alt', $payload)) {
            $media->setAlt($payload['alt']);
        }

        if (array_key_exists('path', $payload)) {
            $media->setPath($payload['path']);
        }

        if (array_key_exists('metaData', $payload)) {
            $metaData = $payload['metaData'];
            if (is_string($metaData) && $metaData !== '') {
                /** @var array<string, mixed>|null $metaData */
                $metaData = JSON::decode($metaData, true);
            }

            $media->setMetaData(is_array($metaData) ? $metaData : null);
        }

        if (array_key_exists('favorite', $payload)) {
            $media->setFavorite($this->normalizeBoolean($payload['favorite']));
        }

        if (array_key_exists('private', $payload)) {
            $media->setPrivate($this->normalizeBoolean($payload['private']));
        }

        $this->entityManager->persist($media);
        $this->entityManager->flush();

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

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $value = strtolower($value);

            if (in_array($value, ['1', 'true'], true)) {
                return true;
            }

            if (in_array($value, ['0', 'false'], true)) {
                return false;
            }
        }

        return null;
    }
}

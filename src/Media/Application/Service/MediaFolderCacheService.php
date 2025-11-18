<?php

declare(strict_types=1);

namespace App\Media\Application\Service;

use App\Media\Infrastructure\Repository\MediaFolderRepository;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @package App\Media\Application\Service
 */
class MediaFolderCacheService
{
    private const string CACHE_PREFIX = 'media_folder_';
    private const int CACHE_TTL = 600;

    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly MediaFolderRepository $mediaFolderRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getRootFolders(string $userId): array
    {
        return $this->cache->get(
            $this->cacheKey($userId, 'root'),
            fn (ItemInterface $item): array => $this->warmupCache(
                $item,
                $userId,
                [
                    'workplaceId' => $userId,
                    'parent' => null,
                ],
                'mediaFolder'
            )
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getFolderByName(string $userId, string $folderName): array
    {
        return $this->cache->get(
            $this->cacheKey($userId, $folderName),
            fn (ItemInterface $item): array => $this->warmupCache(
                $item,
                $userId,
                [
                    'workplaceId' => $userId,
                    'name' => $folderName,
                ],
                'mediaFolder:read'
            )
        );
    }

    public function invalidateUser(string $userId): void
    {
        $this->cache->invalidateTags([$this->cacheTag($userId)]);
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<int|string, mixed>
     */
    private function warmupCache(
        ItemInterface $item,
        string $userId,
        array $criteria,
        string $serializationGroup,
    ): array {
        $item->expiresAfter(self::CACHE_TTL);
        $item->tag($this->cacheTag($userId));

        $mediaFolders = $this->mediaFolderRepository->findBy($criteria);

        return JSON::decode(
            $this->serializer->serialize(
                $mediaFolders,
                'json',
                [
                    'groups' => $serializationGroup,
                ]
            ),
            true
        );
    }

    private function cacheKey(string $userId, string $suffix): string
    {
        return sprintf('%s%s_%s', self::CACHE_PREFIX, $userId, md5($suffix));
    }

    private function cacheTag(string $userId): string
    {
        return sprintf('%s%s', self::CACHE_PREFIX, $userId);
    }
}

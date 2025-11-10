<?php

declare(strict_types=1);

namespace App\Media\Application\Service;

use Bro\WorldCoreBundle\Domain\Service\Interfaces\ElasticsearchServiceInterface;
use App\Media\Application\Service\Interfaces\MediaElasticsearchServiceInterface;
use App\Media\Domain\Entity\Media;
use App\Media\Infrastructure\Repository\MediaRepository;

/**
 * @package App\Media\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class MediaElasticsearchService implements MediaElasticsearchServiceInterface
{
    public function __construct(
        private ElasticsearchServiceInterface $elasticsearchService,
        private MediaRepository $mediaRepository
    ) {
    }

    /**
     * @param Media $media
     */
    public function indexMediaInElasticsearch(Media $media): void
    {
        $document = [
            'id' => $media->getId(),
            'path' => $media->getPath()
        ];

        $this->elasticsearchService->index(
            'medias',
            $media->getId(),
            $document
        );
    }

    public function searchMedias(string $query): array
    {
        $response = $this->elasticsearchService->search(
            'medias',
            [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => $this->mediaRepository->getSearchColumns(),
                    ],
                ],
            ],
        );

        return array_map(fn ($hit) => $hit['_source'], $response['hits']['hits']);
    }

    public function deleteIndex(string $indexName): void
    {
        $this->elasticsearchService->deleteIndex($indexName);
    }
}

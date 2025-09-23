<?php

declare(strict_types=1);

namespace App\Tests\Unit\Media\Application\Service;

use App\General\Domain\Service\Interfaces\ElasticsearchServiceInterface;
use App\Media\Application\Service\MediaElasticsearchService;
use App\Media\Infrastructure\Repository\MediaRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaElasticsearchServiceTest extends TestCase
{
    /**
     * @var ElasticsearchServiceInterface&MockObject
     */
    private ElasticsearchServiceInterface $elasticsearchService;

    /**
     * @var MediaRepository&MockObject
     */
    private MediaRepository $mediaRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elasticsearchService = $this->createMock(ElasticsearchServiceInterface::class);
        $this->mediaRepository = $this->createMock(MediaRepository::class);
    }

    public function testSearchMediasReturnsExpectedHits(): void
    {
        $searchColumns = ['title', 'fileName', 'path', 'contextKey', 'mimeType', 'alt'];
        $expectedHits = [
            ['_source' => ['id' => 'uuid-1', 'title' => 'Image 1', 'path' => '/images/1.jpg']],
            ['_source' => ['id' => 'uuid-2', 'title' => 'Image 2', 'path' => '/images/2.jpg']],
        ];

        $this->mediaRepository
            ->expects(self::once())
            ->method('getSearchColumns')
            ->willReturn($searchColumns);

        $this->elasticsearchService
            ->expects(self::once())
            ->method('search')
            ->with(
                'medias',
                [
                    'query' => [
                        'multi_match' => [
                            'query' => 'image',
                            'fields' => $searchColumns,
                        ],
                    ],
                ],
            )
            ->willReturn([
                'hits' => [
                    'hits' => $expectedHits,
                ],
            ]);

        $service = new MediaElasticsearchService($this->elasticsearchService, $this->mediaRepository);

        self::assertSame(
            [
                ['id' => 'uuid-1', 'title' => 'Image 1', 'path' => '/images/1.jpg'],
                ['id' => 'uuid-2', 'title' => 'Image 2', 'path' => '/images/2.jpg'],
            ],
            $service->searchMedias('image')
        );
    }
}

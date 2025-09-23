<?php

declare(strict_types=1);

namespace App\Tests\Unit\Media\Application\Service;

use App\Media\Application\Service\MediaElasticsearchService;
use App\Media\Application\Service\MediaStoreService;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Entity\MediaFolder;
use App\Media\Infrastructure\Repository\MediaFolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

class MediaStoreServiceTest extends TestCase
{
    private FilesystemOperator&MockObject $filesystem;

    private RequestStack $requestStack;

    private MessageBusInterface&MockObject $messageBus;

    private EntityManagerInterface&MockObject $entityManager;

    private MediaElasticsearchService&MockObject $mediaElasticsearchService;

    private MediaFolderRepository&MockObject $mediaFolderRepository;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->requestStack = new RequestStack();
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mediaElasticsearchService = $this->createMock(MediaElasticsearchService::class);
        $this->mediaFolderRepository = $this->createMock(MediaFolderRepository::class);
    }

    public function testPopulateMediaDoesNotThrowWithInvalidUserId(): void
    {
        $service = $this->createService();
        $media = new Media();

        $method = new \ReflectionMethod(MediaStoreService::class, 'populateMedia');
        $method->setAccessible(true);

        $result = $method->invoke($service, $media, 'not-a-valid-uuid');

        self::assertInstanceOf(Media::class, $result);
        self::assertInstanceOf(UuidInterface::class, $result->getUserId());
        self::assertInstanceOf(UuidInterface::class, $result->getWorkplaceId());
        self::assertSame($result->getUserId()?->toString(), $result->getWorkplaceId()?->toString());
    }

    public function testGetMediaFolderCreatesFolderWithGeneratedUuidWhenUserIdIsInvalid(): void
    {
        $invalidUserId = 'not-a-valid-uuid';
        $request = new Request([], ['mediaFolder' => 'folder-name']);

        $this->mediaFolderRepository
            ->expects(self::once())
            ->method('find')
            ->with('folder-name')
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(MediaFolder::class));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $service = $this->createService();

        $method = new \ReflectionMethod(MediaStoreService::class, 'getMediaFolder');
        $method->setAccessible(true);

        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $method->invoke($service, $request, $invalidUserId);

        self::assertInstanceOf(MediaFolder::class, $mediaFolder);
        $workplaceId = $mediaFolder->getWorkplaceId();
        self::assertInstanceOf(UuidInterface::class, $workplaceId);
        self::assertSame($workplaceId->toString() . '/folder-name/', $mediaFolder->getPath());
    }

    private function createService(): MediaStoreService
    {
        return new MediaStoreService(
            $this->filesystem,
            $this->requestStack,
            $this->messageBus,
            $this->entityManager,
            $this->mediaElasticsearchService,
            $this->mediaFolderRepository,
        );
    }
}

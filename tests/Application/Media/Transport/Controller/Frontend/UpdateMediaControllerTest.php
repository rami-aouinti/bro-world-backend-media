<?php

declare(strict_types=1);

namespace App\Tests\Application\Media\Transport\Controller\Frontend;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Entity\MediaFolder;
use App\Media\Infrastructure\Repository\MediaRepository;
use App\Tests\TestCase\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Override;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Tests
 */
class UpdateMediaControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private MediaRepository $mediaRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->mediaRepository = $container->get(MediaRepository::class);
    }

    #[TestDox('PUT /v1/platform/media/{id} updates persisted media attributes.')]
    public function testThatValidPutRequestUpdatesMedia(): void
    {
        $media = $this->createMediaFixture();
        $mediaId = $media->getId();

        $this->entityManager->clear();

        $client = $this->getTestClient('john-logged', 'password-logged');

        $newUserId = Uuid::uuid4()->toString();
        $payload = [
            'title' => 'Updated title',
            'alt' => 'Updated alt text',
            'path' => '/medias/updated-image.png',
            'metaData' => [
                'title' => 'Updated title',
            ],
            'favorite' => true,
            'private' => false,
            'userId' => $newUserId,
        ];

        $client->request(
            method: 'PUT',
            uri: '/v1/platform/media/' . $mediaId,
            content: JSON::encode($payload),
        );

        $response = $client->getResponse();
        $content = $response->getContent();

        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), "Response:\n" . $response);

        $responseData = JSON::decode($content, true);

        self::assertIsArray($responseData);
        self::assertSame($payload['title'], $responseData['title']);
        self::assertSame($payload['alt'], $responseData['alt']);
        self::assertSame($payload['path'], $responseData['path']);
        self::assertSame($payload['favorite'], $responseData['favorite']);
        self::assertSame($payload['private'], $responseData['private']);
        self::assertSame($newUserId, $responseData['userId']);
        self::assertSame($payload['metaData']['title'], $responseData['metaData']['title']);

        /** @var Media|null $updatedMedia */
        $updatedMedia = $this->mediaRepository->find($mediaId);

        self::assertInstanceOf(Media::class, $updatedMedia);
        self::assertSame($payload['title'], $updatedMedia->getTitle());
        self::assertSame($payload['alt'], $updatedMedia->getAlt());
        self::assertSame($payload['path'], $updatedMedia->getPath());
        self::assertSame($payload['favorite'], $updatedMedia->getFavorite());
        self::assertSame($payload['private'], $updatedMedia->isPrivate());
        self::assertSame($newUserId, $updatedMedia->getUserId()?->toString());
        self::assertSame($payload['metaData'], $updatedMedia->getMetaData());
    }

    private function createMediaFixture(): Media
    {
        $mediaFolder = $this->entityManager->getRepository(MediaFolder::class)->findOneBy([]);
        self::assertInstanceOf(MediaFolder::class, $mediaFolder);

        $media = new Media();
        $media->setUserId(Uuid::uuid4());
        $media->setWorkplaceId(Uuid::uuid4());
        $media->setContextKey($mediaFolder->getName());
        $media->setContextId($mediaFolder->getId());
        $media->setMimeType('image/png');
        $media->setFileExtension('png');
        $media->setFileSize(512);
        $media->setMetaData(['title' => 'Initial title']);
        $media->setFileName('initial.png');
        $media->setTitle('Initial title');
        $media->setAlt('Initial alt text');
        $media->setMediaType('image');
        $media->setFavorite(false);
        $media->setPrivate(true);
        $media->setPath('/medias/initial-image.png');
        $media->setMediaFolder($mediaFolder);

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }
}

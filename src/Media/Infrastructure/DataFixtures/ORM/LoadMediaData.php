<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\DataFixtures\ORM;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Entity\MediaFolder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Override;
use Ramsey\Uuid\Uuid;
use Throwable;

use function array_map;

/**
 * @package App\User
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class LoadMediaData extends Fixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @throws Throwable
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        // Create entities
        $folders = $this->createMedia($manager);

        print_r($folders);
        // Flush database changes
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     */
    #[Override]
    public function getOrder(): int
    {
        return 1;
    }

    /**
     * Method to create User entity with specified role.
     *
     * @throws Throwable
     */
    private function createMedia(ObjectManager $manager): array
    {


        // Folder for Documents
        $documentFolder = new MediaFolder();
        $documentFolder->setWorkplaceId(Uuid::uuid1());
        $documentFolder->setName('documents');
        $documentFolder->setUseParentConfiguration(false);
        $documentFolder->setPath('uploads/documents/');
        $manager->persist($documentFolder);

        // Folder for Images
        $imagesFolder = new MediaFolder();
        $imagesFolder->setWorkplaceId(Uuid::uuid1());
        $imagesFolder->setName('images');
        $imagesFolder->setUseParentConfiguration(false);
        $imagesFolder->setPath('uploads/images/');
        $manager->persist($imagesFolder);

        // Folder for Videos
        $videosFolder = new MediaFolder();
        $videosFolder->setWorkplaceId(Uuid::uuid1());
        $videosFolder->setName('videos');
        $videosFolder->setUseParentConfiguration(false);
        $videosFolder->setPath('uploads/videos/');
        $manager->persist($videosFolder);

        return [$documentFolder->getId(), $imagesFolder->getId(), $videosFolder->getId()];
    }
}

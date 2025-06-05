<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Media\Domain\Entity\MediaThumbnail;

/**
 * @method MediaThumbnail|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaThumbnail|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaThumbnail[]    findAll()
 * @method MediaThumbnail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaThumbnailRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, MediaThumbnail::class);
    }

    public function save(MediaThumbnail $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}

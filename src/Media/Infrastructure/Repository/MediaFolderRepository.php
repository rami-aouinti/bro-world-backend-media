<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Media\Domain\Entity\MediaFolder;

/**
 * @method MediaFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaFolder[]    findAll()
 * @method MediaFolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaFolderRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, MediaFolder::class);
    }
}

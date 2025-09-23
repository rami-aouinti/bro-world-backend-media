<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Repository;

use App\General\Infrastructure\Repository\BaseRepository;
use App\Media\Domain\Entity\Media as Entity;
use App\Media\Domain\Repository\Interfaces\MediaRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package App\Media
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 * @codingStandardsIgnoreStart
 *
 * @method Entity|null find(string $id, ?int $lockMode = null, ?int $lockVersion = null, ?string $entityManagerName = null)
 * @method Entity|null findAdvanced(string $id, string | int | null $hydrationMode = null, string|null $entityManagerName = null)
 * @method Entity|null findOneBy(array $criteria, ?array $orderBy = null, ?string $entityManagerName = null)
 * @method Entity[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?string $entityManagerName = null)
 * @method Entity[] findByAdvanced(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?array $search = null, ?string $entityManagerName = null)
 * @method Entity[] findAll(?string $entityManagerName = null)
 *
 * @codingStandardsIgnoreEnd
 */
class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected static array $searchColumns = [
        'title',
        'fileName',
        'path',
        'contextKey',
        'mimeType',
        'alt',
    ];

    /**
     * @psalm-var class-string
     */
    protected static string $entityName = Entity::class;

    public function __construct(
        protected ManagerRegistry $managerRegistry
    ) {
    }
}

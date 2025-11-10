<?php

declare(strict_types=1);

namespace App\Media\Application\Resource;

use Bro\WorldCoreBundle\Application\DTO\Interfaces\RestDtoInterface;
use Bro\WorldCoreBundle\Application\Rest\RestResource;
use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use App\Media\Domain\Entity\Media as Entity;
use App\Media\Domain\Repository\Interfaces\MediaRepositoryInterface as Repository;
use App\Media\Infrastructure\Repository\MediaRepository;


/**
 * @package App\Media
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 * @codingStandardsIgnoreStart
 *
 * @method Entity getReference(string $id, ?string $entityManagerName = null)
 * @method MediaRepository getRepository()
 * @method Entity[] find(?array $criteria = null, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?array $search = null, ?string $entityManagerName = null)
 * @method Entity|null findOne(string $id, ?bool $throwExceptionIfNotFound = null, ?string $entityManagerName = null)
 * @method Entity|null findOneBy(array $criteria, ?array $orderBy = null, ?bool $throwExceptionIfNotFound = null, ?string $entityManagerName = null)
 * @method Entity create(RestDtoInterface $dto, ?bool $flush = null, ?bool $skipValidation = null, ?string $entityManagerName = null)
 * @method Entity update(string $id, RestDtoInterface $dto, ?bool $flush = null, ?bool $skipValidation = null, ?string $entityManagerName = null)
 * @method Entity patch(string $id, RestDtoInterface $dto, ?bool $flush = null, ?bool $skipValidation = null, ?string $entityManagerName = null)
 * @method Entity delete(string $id, ?bool $flush = null, ?string $entityManagerName = null)
 * @method Entity save(EntityInterface $entity, ?bool $flush = null, ?bool $skipValidation = null, ?string $entityManagerName = null)
 *
 * @codingStandardsIgnoreEnd
 */
class MediaResource extends RestResource
{
    /**
     * @param Repository $repository
     */
    public function __construct(
        Repository $repository,
    ) {
        parent::__construct($repository);
    }
}

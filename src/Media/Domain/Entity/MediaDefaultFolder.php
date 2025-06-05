<?php

declare(strict_types=1);

namespace App\Media\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use App\Media\Domain\Entity\Traits\WorkplaceIdTrait;
use Override;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Throwable;

/**
 * @package App\Media\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
#[ORM\Table(name: 'platform_media_default_folder')]
class MediaDefaultFolder implements EntityInterface
{
    use Uuid;
    use Timestampable;

    public const string SET_USER_MEDIA = 'media';

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'Media',
        'Media.id'
    ])]
    private UuidInterface $id;
    use WorkplaceIdTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private string $entity;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        if (empty($entity)) {
            throw new InvalidArgumentException('Entity cannot be empty.');
        }

        if (strlen($entity) > 255) {
            throw new InvalidArgumentException('Entity length cannot exceed 255 characters.');
        }

        $this->entity = $entity;

        return $this;
    }
}

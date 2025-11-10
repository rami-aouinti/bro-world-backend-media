<?php

declare(strict_types=1);

namespace App\Media\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use App\Media\Domain\Entity\Traits\WorkplaceIdTrait;
use Override;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Throwable;

/**
 * Class MediaThumbnailSize
 *
 * @package App\Media\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
#[ORM\Table(name: 'platform_media_thumbnail_size')]
class MediaThumbnailSize implements EntityInterface
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

    #[ORM\Column(type: 'integer')]
    private int $width;

    #[ORM\Column(type: 'integer')]
    private int $height;

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

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setWidth(int $width): void
    {
        if ($width <= 0) {
            throw new InvalidArgumentException('Width must be a positive integer.');
        }
        $this->width = $width;
    }

    public function setHeight(int $height): void
    {
        if ($height <= 0) {
            throw new InvalidArgumentException('Height must be a positive integer.');
        }
        $this->height = $height;
    }
}

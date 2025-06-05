<?php

declare(strict_types=1);

namespace App\Media\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Media\Domain\Entity\Traits\WorkplaceIdTrait;
use Throwable;

/**
 * Class MediaThumbnail
 *
 * @package App\Media\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
#[ORM\Table(name: 'platform_media_thumbnail')]
class MediaThumbnail implements EntityInterface
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
    #[Assert\NotNull]
    #[Assert\Positive]
    private int $width;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\Positive]
    private int $height;

    #[ORM\Column(type: 'string', length: 2048)]
    #[Assert\NotNull]
    #[Assert\Length(max: 2048)]
    private string $path;

    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist', 'remove'], inversedBy: 'thumbnails')]
    #[Assert\NotNull]
    private ?Media $media = null;

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

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(Media $media): self
    {
        $this->media = $media;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Media\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Override;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Media\Domain\Entity\Traits\WorkplaceIdTrait;
use Throwable;
use App\Media\Infrastructure\Repository\MediaFolderRepository;

/**
 * @package App\Media\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: MediaFolderRepository::class)]
#[ORM\Table(name: 'platform_media_folder')]
#[ORM\HasLifecycleCallbacks]
class MediaFolder implements EntityInterface
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
        'mediaFolder',
        'Media',
        'Media.id'
    ])]
    private UuidInterface $id;

    use WorkplaceIdTrait;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups([
        'mediaFolder',
        'Media',
        'Media.id'
    ])]
    private ?string $name = "";

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 0)]
    #[Groups(['mediaFolder'])]
    private int $childCount = 0;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['mediaFolder'])]
    private ?bool $useParentConfiguration = false;

    #[ORM\Column(type: 'string', length: 2048)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 2048)]
    #[Groups([
        'mediaFolder',
        'Media',
        'Media.id'
    ])]
    private ?string $path = "";

    #[ORM\Column(type: 'boolean')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?bool $private = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?bool $favorite = false;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?MediaFolder $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist', 'remove'])]
    #[Groups(['mediaFolder'])]
    private ?Collection $children;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['mediaFolder'])]
    private $mediaFolderConfiguration = null;

    #[ORM\OneToMany(mappedBy: 'mediaFolder', targetEntity: Media::class, cascade: ['persist', 'remove'])]
    #[Groups(['mediaFolder'])]
    private ?Collection $media;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->media = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getChildCount(): ?int
    {
        return $this->childCount;
    }

    public function setChildCount(?int $childCount): self
    {
        if ($childCount < 0) {
            throw new InvalidArgumentException('Child count cannot be negative.');
        }
        $this->childCount = $childCount;

        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): void
    {
        $this->private = $private;
    }

    public function getFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(?bool $favorite): void
    {
        $this->favorite = $favorite;
    }


    public function isUseParentConfiguration(): ?bool
    {
        return $this->useParentConfiguration;
    }

    public function setUseParentConfiguration(?bool $useParentConfiguration): self
    {
        $this->useParentConfiguration = $useParentConfiguration;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function addMedia(?Media $media): self
    {
        if (!$this->media->contains($media)) {
            $this->media[] = $media;
            $media->setMediaFolder($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->media->removeElement($media)) {
            // Set the owning side to null (unless already changed)
            if ($media->getMediaFolder() === $this) {
                $media->setMediaFolder(null);
            }
        }

        return $this;
    }

    public function getMedia(): ?Collection
    {
        return $this->media;
    }



    public function getMediaFolderConfiguration()
    {
        return $this->mediaFolderConfiguration;
    }

    public function setMediaFolderConfiguration(mixed $mediaFolderConfiguration): self
    {
        $this->mediaFolderConfiguration = $mediaFolderConfiguration;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Media\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Media\Domain\Entity\Traits\WorkplaceIdTrait;
use Throwable;
use App\Media\Infrastructure\Repository\MediaRepository;

/**
 * @package App\Media\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
#[ORM\Table(name: 'platform_media')]
class Media implements EntityInterface
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
        'Media.id',
        'Media:create',
        'mediaFolder:read'
    ])]
    private UuidInterface $id;

    use WorkplaceIdTrait;

    #[ORM\Column(type: 'uuid', nullable: true)]
    #[Groups(['default:read', 'Media',
        'mediaFolder:read'])]
    private ?UuidInterface $userId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['default:read', 'Media',
        'mediaFolder:read'])]
    private string $contextKey;

    #[ORM\Column(type: 'uuid', nullable: true)]
    #[Assert\NotNull]
    #[Groups(['default:read', 'Media',
        'mediaFolder:read'])]
    private ?UuidInterface $contextId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['default:read', 'Media',
        'mediaFolder:read'])]
    private ?string $mimeType = "";

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?string $fileExtension = "";

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ORM\Column(type: 'integer')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?int $fileSize = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?array $metaData = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?string $fileName = "";


    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?string $title = "";

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?string $alt = "";

    #[Assert\NotNull]
    #[ORM\Column(type: 'blob')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private $mediaType = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private $thumbnailsRo = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?bool $private = false;

    #[Assert\NotBlank]
    #[Assert\Length(max: 2048)]
    #[ORM\Column(type: 'string', length: 2048)]
    #[Groups(['default:read', 'Media', 'Media:create',
        'mediaFolder'])]
    private ?string $path = "";

    #[ORM\ManyToOne(targetEntity: MediaFolder::class, cascade: ['persist'], inversedBy: 'media')]
    #[Groups(['default:read', 'Media'])]
    private ?MediaFolder $mediaFolder = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany(
        mappedBy: 'media',
        targetEntity: MediaThumbnail::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Groups(['default:read', 'Media',
        'mediaFolder'])]
    private ?Collection $thumbnails;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->thumbnails = new ArrayCollection();
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?UuidInterface
    {
        return $this->userId;
    }

    public function setUserId(?UuidInterface $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getContextKey(): string
    {
        return $this->contextKey;
    }

    public function setContextKey(string $contextKey): void
    {
        $this->contextKey = $contextKey;
    }

    public function getContextId(): ?UuidInterface
    {
        return $this->contextId;
    }

    public function setContextId(?UuidInterface $contextId): void
    {
        $this->contextId = $contextId;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(?string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function setMetaData(?array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): void
    {
        $this->alt = $alt;
    }

    public function getMediaType(): false|string|null
    {
        if ($this->mediaType) {
            $content = '';
            if (is_resource($this->mediaType)) {
                $size = 1000;
                $offset = 0;
                while (true) {
                    $read = stream_get_contents($this->mediaType, $size, $offset);
                    if ($read === false) {
                        throw new RuntimeException('Unable to read from resource');
                    }
                    $content .= $read;
                    if (strlen($read) < $size) {
                        break;
                    }
                    $offset += $size;
                }
            } else {
                $content = $this->mediaType;
            }

            return $content;
        }
        return null;

    }

    /**
     * @param $mediaType
     *
     * @return $this
     */
    public function setMediaType($mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getThumbnailsRo(): false|string|null
    {
        if ($this->thumbnailsRo) {
            $content = '';
            if (is_resource($this->thumbnailsRo)) {
                $size = 1000;
                $offset = 0;
                while (true) {
                    $read = stream_get_contents($this->thumbnailsRo, $size, $offset);
                    if ($read === false) {
                        throw new RuntimeException('Unable to read from resource');
                    }
                    $content .= $read;
                    if (strlen($read) < $size) {
                        break;
                    }
                    $offset += $size;
                }
            } else {
                $content = $this->thumbnailsRo;
            }

            return $content;
        }
        return null;
    }

    /**
     * @param $thumbnailsRo
     *
     * @return $this
     */
    public function setThumbnailsRo($thumbnailsRo): self
    {
        $this->thumbnailsRo = $thumbnailsRo;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMediaFolder(): ?MediaFolder
    {
        return $this->mediaFolder;
    }

    public function setMediaFolder(?MediaFolder $mediaFolder): self
    {
        $this->mediaFolder = $mediaFolder;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function addThumbnail(MediaThumbnail $thumbnail): static
    {
        if (!$this->thumbnails->contains($thumbnail)) {
            $this->thumbnails->add($thumbnail);
            $thumbnail->setMedia($this);
        }

        return $this;
    }

    public function getThumbnails(): Collection
    {
        return $this->thumbnails;
    }

    public function setThumbnails(?Collection $thumbnails): void
    {
        $this->thumbnails = $thumbnails;
    }
}

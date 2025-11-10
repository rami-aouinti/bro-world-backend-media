<?php

declare(strict_types=1);

namespace App\Media\Application\DTO\Media;

use Bro\WorldCoreBundle\Application\DTO\Interfaces\RestDtoInterface;
use Bro\WorldCoreBundle\Application\DTO\RestDto;
use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use App\Media\Domain\Entity\Media as Entity;
use Override;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Media
 *
 * @method self|RestDtoInterface get(string $id)
 * @method self|RestDtoInterface patch(RestDtoInterface $dto)
 * @method Entity|EntityInterface update(EntityInterface $entity)
 */
class Media extends RestDto
{
    protected ?UuidInterface $userId = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    protected string $title = '';

    #[Assert\NotBlank]
    protected string $alt = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 2048)]
    protected string $path = '';

    #[Assert\Type('array')]
    protected ?array $metaData = null;

    #[Assert\Type('bool')]
    protected ?bool $favorite = null;

    #[Assert\Type('bool')]
    protected ?bool $private = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->setVisited('title');
        $this->title = $title;

        return $this;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->setVisited('alt');
        $this->alt = $alt;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->setVisited('path');
        $this->path = $path;

        return $this;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function setMetaData(?array $metaData): self
    {
        $this->setVisited('metaData');
        $this->metaData = $metaData;

        return $this;
    }

    public function getFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(?bool $favorite): self
    {
        $this->setVisited('favorite');
        $this->favorite = $favorite;

        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): self
    {
        $this->setVisited('private');
        $this->private = $private;

        return $this;
    }

    public function getUserId(): ?UuidInterface
    {
        return $this->userId;
    }

    public function setUserId(?UuidInterface $userId): self
    {
        $this->setVisited('userId');
        $this->userId = $userId;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityInterface|Entity $entity
     */
    #[Override]
    public function load(EntityInterface $entity): self
    {
        if ($entity instanceof Entity) {
            $this->id = $entity->getId();
            $this->userId = $entity->getUserId();
            $this->title = $entity->getTitle();
            $this->alt = $entity->getAlt();
            $this->path = $entity->getPath();
            $this->metaData = $entity->getMetaData();
            $this->favorite = $entity->getFavorite();
            $this->private = $entity->isPrivate();
        }

        return $this;
    }
}

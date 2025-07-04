<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Media\Application\Service\MediaService;
use App\Media\Application\Service\ThumbnailService;
use App\Media\Application\Transformer\FileTransformer;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Entity\MediaFolder;
use App\Media\Domain\Repository\Interfaces\MediaRepositoryInterface;
use App\Media\Infrastructure\Repository\MediaFolderRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use JsonException;
use League\Flysystem\FilesystemException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class PostMediaFolderController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private ThumbnailService $mediaThumbnailGenerator,
        private MediaRepositoryInterface $mediaRepository,
        private MediaFolderRepository $mediaFolderRepository
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @param SymfonyUser $symfonyUser
     * @param Request     $request
     *
     * @throws JsonException
     * @return JsonResponse
     */
    #[Route(
        path: '/v1/platform/mediaFolder',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        $mediaFolder = new MediaFolder();

        $mediaFolder->setName($request->request->get('name'));
        $mediaFolder->setWorkplaceId(Uuid::fromString($symfonyUser->getUserIdentifier()));

        if($request->request->get('mediaFolder')) {
            $mediaFolderParent = $this->mediaFolderRepository->find(Uuid::fromString($request->request->get('mediaFolder')));
            $mediaFolder->setParent($mediaFolderParent);
            $mediaFolder->setPath($mediaFolderParent?->getPath() . $request->request->get('name') .'/');

        } else {
            $mediaFolderParent = $this->mediaFolderRepository->findOneBy(
                [
                    'workplaceId' => $symfonyUser->getUserIdentifier(),
                    'name' => 'General'
                ]
            );
            if(!$mediaFolderParent) {
                $mediaFolderParent = new MediaFolder();
                $mediaFolderParent->setName('General');
                $mediaFolderParent->setWorkplaceId(Uuid::fromString($symfonyUser->getUserIdentifier()));
                $mediaFolderParent->setPath($symfonyUser->getUserIdentifier() . '/');
                $this->entityManager->persist($mediaFolderParent);
                $this->entityManager->flush();
            }
            $mediaFolder->setParent($mediaFolderParent);
            $mediaFolder->setPath($mediaFolderParent->getPath() . $request->request->get('name') .'/');
        }

        $this->entityManager->persist($mediaFolder);
        $this->entityManager->flush();

        $output = JSON::decode(
            $this->serializer->serialize(
                $mediaFolder,
                'json',
                [
                    'groups' => 'mediaFolder:read',
                ]
            ),
            true,
        );
        return new JsonResponse($output);
    }

    /**
     * @param string  $id
     * @param Request $request
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws FilesystemException
     * @return null|bool
     */
    protected function generateThumbnail(string $id, Request $request): ?bool
    {
        $media = $this->mediaRepository->find($id);

        if ($media->getMediaType() === "video") {
            $this->mediaThumbnailGenerator->generateVideoThumbnail($media->getPath(), 200, $media->getId());
        } else {
            $this->mediaThumbnailGenerator->generateImageThumbnail($media->getPath(), 200, $media->getId());
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use App\General\Domain\Utils\JSON;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\Entity\MediaFolder;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\MediaFolder
 */
#[AsController]
#[OA\Tag(name: 'MediaFolder')]
readonly class UpdateMediaFolderController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get current user media data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws Exception
     * @throws ExceptionInterface
     */
    #[Route(
        path: '/v1/platform/mediaFolder/{mediaFolder}',
        methods: [Request::METHOD_PUT],
    )]
    public function __invoke(MediaFolder $mediaFolder, Request $request): JsonResponse
    {
        if($request->request->get('name')) {
            $mediaFolder->setName($request->request->get('name'));
        }

        $this->entityManager->persist($mediaFolder);
        $this->entityManager->flush();

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $mediaFolder,
                'json',
                [
                    'groups' => 'MediaFolder',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}

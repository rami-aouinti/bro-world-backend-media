<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Frontend;

use App\General\Domain\Utils\JSON;
use App\Media\Domain\Entity\Media;
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
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Media
 */
#[AsController]
#[OA\Tag(name: 'Media')]
readonly class UpdateMediaController
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
     */
    #[Route(
        path: '/v1/platform/media/{media}',
        methods: [Request::METHOD_PUT],
    )]
    public function __invoke(Media $media, Request $request): JsonResponse
    {
        if($request->request->get('userId')) {
            $userId = $request->request->get('userId');
            $uuidUser = ($userId && Uuid::isValid($userId)) ? Uuid::fromString($userId) : Uuid::uuid1();
            $media->setUserId($uuidUser);
        }
        if($request->request->get('phone')) {
            $media->setPhone($request->request->get('phone'));
        }
        if($request->request->get('title')) {
            $media->setTitle($request->request->get('title'));
        }
        if($request->request->get('description')) {
            $media->setDescription($request->request->get('description'));
        }
        if($request->request->get('photo')) {
            $photoId = $request->request->get('photo');
            $uuidPhoto = ($photoId && Uuid::isValid($photoId)) ? Uuid::fromString($photoId) : Uuid::uuid1();
            $media->setPhoto($uuidPhoto);
        }
        if($request->request->get('birthday')) {
            $birthday = $request->request->get('birthday');
            $dateBirthday = new DateTime($birthday);
            $media->setBirthday($dateBirthday);
        }
        if($request->request->get('gender')) {
            $media->setGender($request->request->get('gender'));
        }
        if($request->request->get('googleId')) {
            $media->setGoogleId($request->request->get('googleId'));
        }
        if($request->request->get('githubId')) {
            $media->setGithubId($request->request->get('githubId'));
        }

        if($request->request->get('githubUrl')) {
            $media->setGithubUrl($request->request->get('githubUrl'));
        }

        if($request->request->get('instagramUrl')) {
            $media->setInstagramUrl($request->request->get('instagramUrl'));
        }

        if($request->request->get('linkedInId')) {
            $media->setLinkedInId($request->request->get('linkedInId'));
        }

        if($request->request->get('linkedInUrl')) {
            $media->setLinkedInUrl($request->request->get('linkedInUrl'));
        }

        if($request->request->get('twitterUrl')) {
            $media->setTwitterUrl($request->request->get('twitterUrl'));
        }

        if($request->request->get('facebookUrl')) {
            $media->setFacebookUrl($request->request->get('facebookUrl'));
        }

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $media,
                'json',
                [
                    'groups' => 'Media',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}

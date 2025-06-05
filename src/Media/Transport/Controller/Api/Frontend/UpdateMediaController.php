<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Api\Frontend;

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
    #[OA\RequestBody(
        request: 'body',
        description: 'Credentials object',
        required: true,
        content: new JsonContent(
            properties: [
                new Property(property: 'phone', ref: new Model(type: Media::class, groups: ['Media.phone'])),
                new Property(property: 'userId', ref: new Model(type: Media::class, groups: ['Media.userId'])),
                new Property(property: 'title', ref: new Model(type: Media::class, groups: ['Media.title'])),
                new Property(property: 'description', ref: new Model(type: Media::class, groups: ['Media.description'])),
                new Property(property: 'photo', ref: new Model(type: Media::class, groups: ['Media.photo'])),
                new Property(property: 'birthday', ref: new Model(type: Media::class, groups: ['Media.birthday'])),
                new Property(property: 'gender', ref: new Model(type: Media::class, groups: ['Media.gender'])),
                new Property(property: 'googleId', ref: new Model(type: Media::class, groups: ['Media.googleId'])),
                new Property(property: 'githubId', ref: new Model(type: Media::class, groups: ['Media.githubId'])),
                new Property(property: 'githubUrl', ref: new Model(type: Media::class, groups: ['Media.githubUrl'])),
                new Property(property: 'instagramUrl', ref: new Model(type: Media::class, groups: ['Media.instagramUrl'])),
                new Property(property: 'linkedInId', ref: new Model(type: Media::class, groups: ['Media.linkedInId'])),
                new Property(property: 'linkedInUrl', ref: new Model(type: Media::class, groups: ['Media.linkedInUrl'])),
                new Property(property: 'twitterUrl', ref: new Model(type: Media::class, groups: ['Media.twitterUrl'])),
                new Property(property: 'facebookUrl', ref: new Model(type: Media::class, groups: ['Media.facebookUrl'])),
            ],
            type: 'object',
            example: [
                'phone' => '+33612345678',
                'userId' => '550e8400-e29b-41d4-a716-446655440000',
                'title' => 'Developer Backend Symfony',
                'description' => 'Expert API et microservices.',
                'photo' => '550e8400-e29b-41d4-a716-446655440001',
                'birthday' => '1993-05-14',
                'gender' => 'Men',
                'googleId' => '12345678901234567890',
                'githubId' => '98765432109876543210',
                'githubUrl' => 'https://github.com/johndoe',
                'instagramUrl' => 'https://instagram.com/johndoe',
                'linkedInId' => 'abc123def456ghi789',
                'linkedInUrl' => 'https://linkedin.com/in/johndoe',
                'twitterUrl' => 'https://twitter.com/johndoe',
                'facebookUrl' => 'https://facebook.com/johndoe'
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Media data',
        content: new JsonContent(
            ref: new Model(
                type: Media::class,
                groups: ['Media'],
            ),
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid token (not found or expired)',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 401,
                'message' => 'JWT Token not found',
            ],
        ),
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 403,
                'message' => 'Access denied',
            ],
        ),
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

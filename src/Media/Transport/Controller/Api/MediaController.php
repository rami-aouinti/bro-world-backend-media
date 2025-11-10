<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Api;

use Bro\WorldCoreBundle\Transport\Rest\Controller;
use Bro\WorldCoreBundle\Transport\Rest\ResponseHandler;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions;
use App\Media\Application\DTO\Media\MediaCreate;
use App\Media\Application\DTO\Media\MediaPatch;
use App\Media\Application\DTO\Media\MediaUpdate;
use App\Media\Application\Resource\MediaResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;


/**
 * @package App\Media
 *
 * @method MediaResource getResource()
 * @method ResponseHandler getResponseHandler()
 */
#[AsController]
#[Route(
    path: '/v1/media',
)]
#[OA\Tag(name: 'Media Management')]
class MediaController extends Controller
{
    use Actions\Admin\CountAction;
    use Actions\Admin\FindAction;
    use Actions\Admin\FindOneAction;
    use Actions\Admin\IdsAction;
    use Actions\Root\CreateAction;
    use Actions\Root\PatchAction;
    use Actions\Root\UpdateAction;

    /**
     * @var array<string, string>
     */
    protected static array $dtoClasses = [
        Controller::METHOD_CREATE => MediaCreate::class,
        Controller::METHOD_UPDATE => MediaUpdate::class,
        Controller::METHOD_PATCH => MediaPatch::class,
    ];

    public function __construct(
        MediaResource $resource,
    ) {
        parent::__construct($resource);
    }
}

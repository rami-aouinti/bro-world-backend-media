<?php

declare(strict_types=1);

namespace App\Media\Transport\AutoMapper\Media;

use App\General\Transport\AutoMapper\RestAutoMapperConfiguration;
use App\Media\Application\DTO\Media\MediaCreate;
use App\Media\Application\DTO\Media\MediaPatch;
use App\Media\Application\DTO\Media\MediaUpdate;

/**
 * @package App\User
 */
class AutoMapperConfiguration extends RestAutoMapperConfiguration
{
    /**
     * Classes to use specified request mapper.
     *
     * @var array<int, class-string>
     */
    protected static array $requestMapperClasses = [
        MediaCreate::class,
        MediaUpdate::class,
        MediaPatch::class,
    ];

    public function __construct(
        RequestMapper $requestMapper,
    ) {
        parent::__construct($requestMapper);
    }
}

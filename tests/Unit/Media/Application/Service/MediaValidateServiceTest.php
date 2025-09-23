<?php

declare(strict_types=1);

namespace App\Tests\Unit\Media\Application\Service;

use App\Media\Application\Service\MediaValidateService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function file_exists;
use function file_put_contents;
use function filesize;
use function str_repeat;
use function tempnam;
use function sys_get_temp_dir;
use function unlink;

class MediaValidateServiceTest extends KernelTestCase
{
    public function testAudioFileUnderLimitPassesValidation(): void
    {
        self::bootKernel();

        /** @var MediaValidateService $service */
        $service = static::getContainer()->get(MediaValidateService::class);

        $tempFile = tempnam(sys_get_temp_dir(), 'audio_test_');
        if ($tempFile === false) {
            self::fail('Unable to create temporary file for testing.');
        }

        try {
            file_put_contents($tempFile, str_repeat('0', 512 * 1024));
            self::assertSame(512 * 1024, filesize($tempFile));

            $uploadedFile = new UploadedFile(
                $tempFile,
                'test-audio.mp3',
                'audio/mpeg',
                null,
                true
            );

            $service->checkMimeTypeAndSize($uploadedFile);

            self::assertLessThanOrEqual(200 * 1024 * 1024, $uploadedFile->getSize());
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}

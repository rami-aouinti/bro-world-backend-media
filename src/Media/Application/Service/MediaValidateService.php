<?php

declare(strict_types=1);

namespace App\Media\Application\Service;

use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function in_array;
use function sprintf;

/**
 * @package App\Media\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class MediaValidateService
{
    private const array ALLOWED_MIME_TYPES = [
        'documents' => [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/rtf', 'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet', 'application/x-abiword',
            'application/vnd.ms-excel', 'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/epub+zip', 'application/x-freearc', 'text/plain',
            'text/csv', 'application/json', 'application/xml',
        ],
        'images' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/svg+xml',
            'image/tiff', 'image/webp', 'image/ico', 'image/heic', 'image/heif',
        ],
        'videos' => [
            'video/mp4', 'video/quicktime', 'video/webm', 'video/avi',
            'video/mpeg', 'video/x-matroska', 'video/ogg', 'video/x-flv',
        ],
        'audio' => [
            'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/webm', 'audio/x-flac',
        ],
        'archives' => [
            'application/zip', 'application/x-7z-compressed', 'application/x-rar-compressed',
            'application/x-tar', 'application/gzip', 'application/x-bzip', 'application/x-bzip2',
        ],
        'others' => [
            'application/javascript', 'application/x-sh', 'application/vnd.android.package-archive',
            'application/x-msdownload', 'application/octet-stream',
        ],
    ];

    private const array MAX_FILE_SIZE = [
        'documents' => 10 * 1024 * 1024, // 10 MB
        'images' => 10 * 1024 * 1024,    // 10 MB
        'videos' => 2 * 1024 * 1024 * 1024, // 2 GB
    ];

    /**
     * @throws Exception
     */
    public function scanFile(string $filePath): bool
    {
        $realPath = realpath($filePath);
        if ($realPath === false) {
            throw new RuntimeException('Failed to get the real path of the file: ' . $filePath);
        }

        chmod($realPath, 0644);

        $command = 'clamdscan ' . escapeshellarg($realPath);
        exec($command . ' 2>&1', $output, $returnVar);

        foreach ($output as $line) {
            if (str_contains($line, 'FOUND')) {
                throw new RuntimeException('The file cannot be uploaded and processed because it is suspected to contain a virus.');
            }
        }

        return true;
    }

    /**
     * @throws HttpException
     */
    public function checkMimeTypeAndSize(UploadedFile $file): void
    {
        $fileMimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $category = $this->getFileCategory($fileMimeType);

        if ($category === null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Unsupported file type');
        }

        if ($fileSize > self::MAX_FILE_SIZE[$category]) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, sprintf(
                'File size exceeds the maximum allowed size of %d KB for %s files',
                self::MAX_FILE_SIZE[$category] / (102400),
                $category
            ));
        }
    }

    private function getFileCategory(string $mimeType): ?string
    {
        foreach (self::ALLOWED_MIME_TYPES as $category => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes, true)) {
                return $category;
            }
        }

        return null;
    }
}

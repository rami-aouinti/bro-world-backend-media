<?php

declare(strict_types=1);

namespace App\Media\Application\Util;

use InvalidArgumentException;
use RuntimeException;

/**
 *
 */
trait FileHelperTrait
{
    public function sanitizeFileName(string $filename): string
    {
        $filename = trim($filename);
        $sanitized = preg_replace('/[^A-Za-z0-9_.-]/', '', str_replace(' ', '_', $filename));

        if (empty($sanitized)) {
            throw new InvalidArgumentException('The cleaned file name is invalid.');
        }

        return $sanitized;
    }

    public function ensureDirectoryExists(string $path): string
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new RuntimeException(sprintf('The directory "%s" could not be created.', $path));
            }
        }

        return $path;
    }
}

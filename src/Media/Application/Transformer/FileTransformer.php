<?php

declare(strict_types=1);

namespace App\Media\Application\Transformer;

/**
 * @package App\Media\Transformer
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class FileTransformer
{
    public function transform(array $data): array
    {
        $commonAttributes = [
            'userId' => $data[0]['userId'],
            'contextKey' => $data[0]['contextKey'],
            'contextId' => $data[0]['contextId'],
            'workplaceId' => $data[0]['workplaceId'],
            'thumbnailsRo' => $data[0]['thumbnailsRo'],
            'private' => $data[0]['private'],
            'deletedAt' => $data[0]['deletedAt'],
            'updatedAt' => $data[0]['updatedAt'],
            'createdAt' => $data[0]['createdAt'],
        ];

        $files = array_map(function ($item) {
            return [
                'mimeType' => $item['mimeType'],
                'fileExtension' => $item['fileExtension'],
                'fileSize' => $item['fileSize'],
                'metaData' => $item['metaData'],
                'fileName' => $item['fileName'],
                'mediaType' => $item['mediaType'],
                'path' => $item['path'],
                'thumbnails' => $item['thumbnails'],
                'id' => $item['id'],
            ];
        }, $data);

        return array_merge($commonAttributes, [
            'medias' => $files,
        ]);
    }
}

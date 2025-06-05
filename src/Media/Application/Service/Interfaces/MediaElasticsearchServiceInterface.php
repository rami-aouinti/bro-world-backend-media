<?php

declare(strict_types=1);

namespace App\Media\Application\Service\Interfaces;

use App\Media\Domain\Entity\Media;

/**
 *
 */
interface MediaElasticsearchServiceInterface
{
    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not found.
     *
     * Method is override for performance reasons see link below.
     *
     * @see http://symfony2-document.readthedocs.org/en/latest/cookbook/security/entity_provider.html
     *      #managing-roles-in-the-database
     */
    public function indexMediaInElasticsearch(Media $media): void;

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not found.
     *
     * Method is override for performance reasons see link below.
     *
     * @see http://symfony2-document.readthedocs.org/en/latest/cookbook/security/entity_provider.html
     *      #managing-roles-in-the-database
     */
    public function searchMedias(string $query): array;

    /**
     * Create/update template
     * https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-templates-v1.html
     *
     * @param string $indexName
     *
     * @return void
     */
    public function deleteIndex(string $indexName): void;
}

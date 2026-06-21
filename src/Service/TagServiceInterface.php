<?php

/**
 * Tag service interface.
 */

namespace App\Service;

use App\Entity\Tag;

/**
 * Interface TagServiceInterface.
 */
interface TagServiceInterface
{
    /**
     * Edit tag.
     *
     * @param Tag    $tag   Tag
     * @param string $title Title
     */
    public function edit(Tag $tag, string $title): void;

    /**
     * Delete tag.
     *
     * @param Tag $tag Tag
     */
    public function delete(Tag $tag): void;
}

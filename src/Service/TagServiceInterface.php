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
    public function edit(Tag $tag, string $title): void;

    public function delete(Tag $tag): void;
}

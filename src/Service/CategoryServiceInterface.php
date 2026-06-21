<?php

/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    public function edit(Category $category, string $title): void;

    public function delete(Category $category): void;
}

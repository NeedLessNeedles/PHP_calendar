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
    /**
     * Edit category.
     *
     * @param Category $category Category
     * @param string   $title    Title
     */
    public function edit(Category $category, string $title): void;

    /**
     * Delete category.
     *
     * @param Category $category Category
     */
    public function delete(Category $category): void;
}

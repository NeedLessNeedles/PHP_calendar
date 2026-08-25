<?php

/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     */
    public function getPaginatedList(int $page): PaginationInterface;

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

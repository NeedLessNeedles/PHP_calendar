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
     * Delete category.
     *
     * @param Category $category Category
     */
    public function delete(Category $category): void;

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool;

    /**
     * Can Title for Category be empty?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeEmpty(Category $category): bool;
}

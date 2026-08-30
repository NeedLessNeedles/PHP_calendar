<?php

/**
 * Tag service interface.
 */

namespace App\Service;

use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TagServiceInterface.
 */
interface TagServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Tag $tag Tag entity
     */
    public function save(Tag $tag): void;

    /**
     * Delete tag.
     *
     * @param Tag $tag Tag
     */
    public function delete(Tag $tag): void;

    /**
     * Can Title for Tag be empty?
     *
     * @param Tag $tag Tag entity
     *
     * @return bool Result
     */
    public function canBeEmpty(Tag $tag): bool;
}

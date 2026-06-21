<?php

/**
 * Event service interface.
 */

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface EventServiceInterface.
 */
interface EventServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int         $page       Page number
     * @param int|null    $categoryId Category ID
     * @param string|null $title      Title
     * @param int|null    $tagId      Tag ID
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?int $categoryId = null, ?string $title = null, ?int $tagId = null): PaginationInterface;

    /**
     * Create event.
     *
     * @param Event     $event Event
     * @param User|null $user  User
     */
    public function create(Event $event, ?User $user): void;
}

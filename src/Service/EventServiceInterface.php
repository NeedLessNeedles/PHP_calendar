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
     * @param string|null $status     Status
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?int $categoryId = null, ?string $title = null, ?int $tagId = null, ?string $status = null): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Event $event Event entity
     */
    public function save(Event $event, ?User $user): void;

    /**
     * Delete entity.
     *
     * @param Event $event Event entity
     */
    public function delete(Event $event): void;

    /**
     * Create event.
     *
     * @param Event     $event Event
     * @param User|null $user  User
     */
    public function create(Event $event, ?User $user): void;

    /**
     * Export approved current and upcoming events to ICS format.
     *
     * @return string ICS content
     */
    public function exportToIcs(): string;

}

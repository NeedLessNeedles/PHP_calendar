<?php

/**
 * Admin service interface.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface AdminServiceInterface.
 */
interface AdminServiceInterface
{
    /**
     * Get paginated pending events.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Change password.
     *
     * @param User   $user     User
     * @param string $password Password
     */
    public function changePassword(User $user, string $password): void;

    /**
     * Approve event.
     *
     * @param Event $event Event
     */
    public function approveEvent(Event $event): void;

    /**
     * Reject event.
     *
     * @param Event $event Event
     */
    public function rejectEvent(Event $event): void;

    /**
     * Toggle user block.
     *
     * @param User $targetUser  Target user
     * @param User $currentUser Current user
     */
    public function toggleBlock(User $targetUser, User $currentUser): void;

    /**
     * Count administrators.
     *
     * @return int Number of administrators
     */
    public function countAdmins(): int;

    /**
     * Toggle administrator role.
     *
     * @param User $targetUser Target user
     */
    public function toggleAdminRole(User $targetUser): void;
}

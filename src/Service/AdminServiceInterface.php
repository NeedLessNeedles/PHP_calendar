<?php

/**
 * Admin service interface.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;

/**
 * Interface AdminServiceInterface.
 */
interface AdminServiceInterface
{
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
     * Toggle user block.
     *
     * @param User $targetUser  Target user
     * @param User $currentUser Current user
     */
    public function toggleBlock(User $targetUser, User $currentUser): void;
}

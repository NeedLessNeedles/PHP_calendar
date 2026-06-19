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
    public function changePassword(User $user, string $password): void;

    public function approveEvent(Event $event): void;

}

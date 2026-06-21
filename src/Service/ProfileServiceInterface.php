<?php

/**
 * Profile service interface.
 */

namespace App\Service;

use App\Entity\User;

/**
 * Interface ProfileServiceInterface.
 */
interface ProfileServiceInterface
{
    public function changePassword(User $user, string $password): void;
}

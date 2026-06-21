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
    /**
     * Change password.
     *
     * @param User   $user     User
     * @param string $password Password
     */
    public function changePassword(User $user, string $password): void;
}

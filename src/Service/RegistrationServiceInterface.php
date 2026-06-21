<?php

/**
 * Registration service interface.
 */

namespace App\Service;

use App\Entity\User;

/**
 * Interface RegistrationServiceInterface.
 */
interface RegistrationServiceInterface
{
    /**
     * User registration.
     *
     * @param User   $user          User
     * @param string $plainPassword Plain password
     */
    public function registerUser(User $user, string $plainPassword): void;
}

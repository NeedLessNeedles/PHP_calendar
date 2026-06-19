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
    public function registerUser(User $user, string $plainPassword): void;

}

<?php

/**
 * Profile service.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class ProfileService.
 */
class ProfileService implements ProfileServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Change password.
     *
     * @param User   $user     User
     * @param string $password Password
     */
    public function changePassword(User $user, string $password): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );
    }
}

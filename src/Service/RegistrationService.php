<?php

/**
 * Registration service.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RegistrationService.
 */
class RegistrationService implements RegistrationServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param EntityManagerInterface      $entityManager  Entity manager
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * User registration.
     *
     * @param User   $user          User
     * @param string $plainPassword Plain password
     */
    public function registerUser(User $user, string $plainPassword): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}

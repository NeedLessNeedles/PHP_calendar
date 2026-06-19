<?php
/**
 * Admin service.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminService.
 */
class AdminService implements AdminServiceInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function changePassword(User $user, string $password): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );
    }

    public function approveEvent(Event $event): void
    {
        $event->setStatus('approved');
    }

}

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

    public function toggleBlock(User $targetUser, User $currentUser): void
    {
        if ($targetUser->getId() === $currentUser->getId()) {
            throw new \LogicException('You cannot block yourself.');
        }

        if (in_array('ROLE_ADMIN', $targetUser->getRoles(), true)) {
            throw new \LogicException('You cannot block another admin.');
        }

        $targetUser->setIsBlocked(!$targetUser->isBlocked());
    }

}

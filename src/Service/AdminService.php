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

    /**
     * Approve event.
     *
     * @param Event $event Event
     */
    public function approveEvent(Event $event): void
    {
        $event->setStatus('approved');
    }

    /**
     * Toggle user block.
     *
     * @param User $targetUser  Target user
     * @param User $currentUser Current user
     */
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

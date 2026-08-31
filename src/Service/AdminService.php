<?php

/**
 * Admin service.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;

/**
 * Class AdminService.
 */
class AdminService implements AdminServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param UserRepository              $userRepository User repository
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserRepository $userRepository)
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
        $this->userRepository->save($targetUser);
    }

    /**
     * Count administrators.
     *
     * @return int Number of administrators
     */
    public function countAdmins(): int
    {
        $users = $this->userRepository->findAll();

        $adminsCount = 0;

        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                ++$adminsCount;
            }
        }

        return $adminsCount;
    }

    /**
     * Toggle administrator role.
     *
     * @param User $targetUser Target user
     */
    public function toggleAdminRole(User $targetUser): void
    {
        $roles = $targetUser->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            if ($this->countAdmins() <= 1) {
                throw new \LogicException('Cannot remove administrator role from the last administrator.');
            }

            $roles = array_filter(
                $roles,
                static fn (string $role): bool => 'ROLE_ADMIN' !== $role
            );
        } else {
            $roles[] = 'ROLE_ADMIN';
        }

        $targetUser->setRoles(
            array_values(array_unique($roles))
        );
    }
}

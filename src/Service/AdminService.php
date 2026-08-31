<?php

/**
 * Admin service.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Repository\EventRepository;

/**
 * Class AdminService.
 */
class AdminService implements AdminServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher  Password hasher
     * @param UserRepository              $userRepository  User repository
     * @param EventRepository             $eventRepository event repository
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserRepository $userRepository, private readonly EventRepository $eventRepository)
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
        $this->eventRepository->save($event);
    }

    /**
     * Reject event.
     *
     * @param Event $event Event
     */
    public function rejectEvent(Event $event): void
    {
        $this->eventRepository->delete($event);
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
     * @param User $user User
     */
    public function toggleAdminRole(User $user): void
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $adminCount = $this->userRepository->countAdministrators();

            if ($adminCount <= 1) {
                throw new \LogicException('Cannot remove administrator role from the last administrator.');
            }

            $roles = array_filter(
                $roles,
                static fn (string $role): bool => 'ROLE_ADMIN' !== $role
            );
        } else {
            $roles[] = 'ROLE_ADMIN';
        }

        $user->setRoles(array_values($roles));

        $this->userRepository->save($user);
    }
}

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
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class AdminService.
 */
class AdminService implements AdminServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @varant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 5;

    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher  Password hasher
     * @param UserRepository              $userRepository  User repository
     * @param EventRepository             $eventRepository event repository
     * @param PaginatorInterface $paginator          Paginator
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserRepository $userRepository, private readonly EventRepository $eventRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated pending events.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->eventRepository->queryAll(
                null,
                status: 'pending'
            ),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['event.startDate', 'event.title'],
                'defaultSortFieldName' => 'event.startDate',
                'defaultSortDirection' => 'desc',
            ]
        );
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

<?php

/**
 * Profile service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class ProfileService.
 */
class ProfileService implements ProfileServiceInterface
{
    public const PAGINATOR_ITEMS_PER_PAGE = 2;

    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly PaginatorInterface $paginator, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['user.email'],
                'defaultSortFieldName' => 'user.email',
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
}

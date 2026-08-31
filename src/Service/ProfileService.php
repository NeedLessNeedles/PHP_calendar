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
     * @param UserRepository              $userRepository User repository
     * @param PaginatorInterface          $paginator      Paginator
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
     * Save password.
     *
     * @param User   $user     User
     * @param string $password Password
     */
    public function savePassword(User $user, string $password): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );
        $this->userRepository->save($user);
    }

    /**
     * Save email.
     *
     * @param User   $user  User
     * @param string $email Email
     */
    public function saveEmail(User $user, string $email): void
    {
        $user->setEmail($email);

        $this->userRepository->save($user);
    }

    /**
     * Can User's email be empty?
     *
     * @param string|null $email Email
     *
     * @return bool Result
     */
    public function canBeEmpty(?string $email): bool
    {
        return null !== $email && '' !== trim($email);
    }

    /**
     * Check whether User's email is unique.
     *
     * @param User        $user  User entity
     * @param string|null $email Email
     *
     * @return bool Result
     */
    public function isEmailUnique(User $user, ?string $email): bool
    {
        if (null === $email || '' === trim($email)) {
            return false;
        }

        $existingUser = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if (null === $existingUser) {
            return true;
        }

        return $existingUser->getId() === $user->getId();
    }

    /**
     * Check User's password be empty?.
     *
     * @param string|null $password Password
     *
     * @return bool Result
     */
    public function canPasswordBeEmpty(?string $password): bool
    {
        return null !== $password && '' !== trim($password);
    }

    /**
     * Check if password has at least 8 characters.
     *
     * @param string|null $password Password
     *
     * @return bool Result
     */
    public function isPasswordLongEnough(?string $password): bool
    {
        return null !== $password && mb_strlen($password) >= 8;
    }
}

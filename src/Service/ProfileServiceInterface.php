<?php

/**
 * Profile service interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface ProfileServiceInterface.
 */
interface ProfileServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save password entity.
     *
     * @param User   $user     User
     * @param string $password Password
     */
    public function savePassword(User $user, string $password): void;

    /**
     * Save email.
     *
     * @param User   $user  User
     * @param string $email Email
     */
    public function saveEmail(User $user, string $email): void;

    /**
     * Can User's email be empty?
     *
     * @param string|null $email Email
     *
     * @return bool Result
     */
    public function canBeEmpty(?string $email): bool;

    /**
     * Check whether User's email is unique.
     *
     * @param User        $user  User entity
     * @param string|null $email Email
     *
     * @return bool Result
     */
    public function isEmailUnique(User $user, ?string $email): bool;

    /**
     * Check User's password be empty?.
     *
     * @param string|null $password Password
     *
     * @return bool Result
     */
    public function canPasswordBeEmpty(?string $password): bool;

    /**
     * Check if password has at least 8 characters.
     *
     * @param string|null $password Password
     *
     * @return bool Result
     */
    public function isPasswordLongEnough(?string $password): bool;
}

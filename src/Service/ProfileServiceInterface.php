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
}

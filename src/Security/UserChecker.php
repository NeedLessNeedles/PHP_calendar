<?php

/**
 * User checker.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * Class UserChecker.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Check pre-authentication.
     *
     * @param UserInterface $user User
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException('Account is blocked.');
        }
    }

    /**
     * Check post-authentication (optional).
     *
     * @param UserInterface $user User
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // optional
    }
}

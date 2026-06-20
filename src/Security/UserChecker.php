<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
//    public function checkPreAuth(User $user): void
//    {
//        if ($user->isBlocked()) {
//            throw new CustomUserMessageAccountStatusException('Account is blocked.');
//        }
//    }
//
//    public function checkPostAuth(User $user): void
//    {
//        // optional
//    }
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException('Account is blocked.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // optional
    }
}

<?php

/**
 * Tests for UserChecker.
 */

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * Class UserCheckerTest.
 */
class UserCheckerTest extends TestCase
{
    public function testBlockedUserThrowsException(): void
    {
        $user = new User();
        $user->setIsBlocked(true);
        $checker = new UserChecker();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($user);
    }

    public function testNotBlockedUserPasses(): void
    {
        $user = new User();
        $user->setIsBlocked(false);
        $checker = new UserChecker();

        $checker->checkPreAuth($user);
        $this->assertTrue(true);
    }
}

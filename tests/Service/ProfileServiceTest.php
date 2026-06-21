<?php

/**
 * Tests for ProfileService.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\ProfileService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class ProfileServiceTest.
 */
class ProfileServiceTest extends TestCase
{
    public function testChangePassword(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'new-password')
            ->willReturn('hashed-password');

        $service = new ProfileService($passwordHasher);
        $service->changePassword($user, 'new-password');

        $this->assertSame('hashed-password', $user->getPassword());
    }
}

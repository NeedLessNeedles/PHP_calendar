<?php

/**
 * Tests for RegistrationService.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class RegistrationServiceTest.
 */
class RegistrationServiceTest extends TestCase
{
    public function testRegisterUser(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'plain-password')
            ->willReturn('hashed-password');
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);
        $entityManager
            ->expects($this->once())
            ->method('flush');

        $service = new RegistrationService($passwordHasher, $entityManager);
        $service->registerUser($user, 'plain-password');

        $this->assertSame('hashed-password', $user->getPassword());
    }
}

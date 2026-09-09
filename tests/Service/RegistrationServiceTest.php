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
    /**
     * Test if user can be registered.
     */
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

    /**
     * Test if user can be empty.
     */
    public function testCanBeEmptyReturnsTrueForValidData(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $service = new RegistrationService(
            $this->createStub(UserPasswordHasherInterface::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertTrue(
            $service->canBeEmpty($user, 'plain-password')
        );
    }

    /**
     * Test if empty email is rejected.
     */
    public function testCanBeEmptyReturnsFalseForEmptyEmail(): void
    {
        $user = new User();
        $user->setEmail('   ');

        $service = new RegistrationService(
            $this->createStub(UserPasswordHasherInterface::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertFalse(
            $service->canBeEmpty($user, 'plain-password')
        );
    }

    /**
     * Test if missing email is rejected.
     */
    public function testCanBeEmptyReturnsFalseForMissingEmail(): void
    {
        $user = new User();

        $service = new RegistrationService(
            $this->createStub(UserPasswordHasherInterface::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertFalse(
            $service->canBeEmpty($user, 'plain-password')
        );
    }

    /**
     * Test if missing password is rejected.
     */
    public function testCanBeEmptyReturnsFalseForMissingPassword(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $service = new RegistrationService(
            $this->createStub(UserPasswordHasherInterface::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertFalse(
            $service->canBeEmpty($user, null)
        );
    }

    /**
     * Test if empty password is rejected.
     */
    public function testCanBeEmptyReturnsFalseForEmptyPassword(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $service = new RegistrationService(
            $this->createStub(UserPasswordHasherInterface::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertFalse(
            $service->canBeEmpty($user, '   ')
        );
    }
}

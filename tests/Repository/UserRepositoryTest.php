<?php

/**
 * Tests for UserRepository.
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class UserRepositoryTest.
 */
class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $userRepository;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    /**
     * Constructor test.
     */
    public function testRepositoryCanBeCreated(): void
    {
        self::bootKernel();
        $repo = self::getContainer()->get(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $repo);
    }

    /**
     * Test query builder.
     */
    public function testQueryAll(): void
    {
        $qb = $this->userRepository->queryAll();

        $this->assertStringContainsString(
            'SELECT',
            $qb->getDQL()
        );

        $this->assertStringContainsString(
            'FROM App\Entity\User',
            $qb->getDQL()
        );
    }

    /**
     * Test UpgradePassword.
     */
    public function testUpgradePassword(): void
    {
        $user = new User();
        $user->setEmail('upgrade-password-test-'.uniqid().'@test.com');
        $user->setPassword('old');

        $this->userRepository->upgradePassword($user, 'new-hash');

        $this->assertSame('new-hash', $user->getPassword());
    }

    /**
     * Test upgradePassword with unsupported user.
     */
    public function testUpgradePasswordWithUnsupportedUser(): void
    {
        $user = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string
            {
                return 'old';
            }

            public function getUserIdentifier(): string
            {
                return 'unsupported@test.com';
            }
        };

        $this->expectException(UnsupportedUserException::class);
        $this->userRepository->upgradePassword($user, 'new-hash');
    }
}

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
        $user = new class () implements PasswordAuthenticatedUserInterface {
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

    /**
     * Test saving user.
     */
    public function testSave(): void
    {
        $user = new User();
        $user->setEmail(
            'save-test-'.uniqid().'@test.com'
        );
        $user->setPassword('password');
        $this->userRepository->save($user);
        $this->assertNotNull($user->getId());

        $savedUser = $this->userRepository->find(
            $user->getId()
        );
        $this->assertInstanceOf(
            User::class,
            $savedUser
        );

        $this->assertSame(
            $user->getEmail(),
            $savedUser->getEmail()
        );
    }

    /**
     * Test finding all users.
     */
    public function testFindAllUsers(): void
    {
        $users = $this->userRepository->findAllUsers();
        $this->assertIsArray($users);

        foreach ($users as $user) {
            $this->assertInstanceOf(
                User::class,
                $user
            );
        }
    }

    /**
     * Test counting administrators.
     */
    public function testCountAdministrators(): void
    {
        $initialCount = $this->userRepository->countAdministrators();
        $admin = new User();
        $admin->setEmail(
            'admin-count-test-'.uniqid().'@test.com'
        );
        $admin->setPassword('password');
        $admin->setRoles(['ROLE_ADMIN']);

        $user = new User();
        $user->setEmail(
            'user-count-test-'.uniqid().'@test.com'
        );
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($admin);
        $this->userRepository->save($user);

        $count = $this->userRepository->countAdministrators();

        $this->assertSame(
            $initialCount + 1,
            $count
        );
    }

    /**
     * Test counting administrators excludes regular users.
     */
    public function testCountAdministratorsExcludesRegularUsers(): void
    {
        $initialCount = $this->userRepository->countAdministrators();

        $user = new User();
        $user->setEmail(
            'regular-count-test-'.uniqid().'@test.com'
        );
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($user);

        $this->assertSame(
            $initialCount,
            $this->userRepository->countAdministrators()
        );
    }
}

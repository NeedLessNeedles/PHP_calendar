<?php

/**
 * Tests for UserRepository.
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserRepositoryTest.
 */
class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $userRepository;

    /**
     * Constructor test.
     */
    public function testRepositoryCanBeCreated(): void
    {
        self::bootKernel();
        $repo = self::getContainer()->get(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $repo);
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testUpgradePassword(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('old');

        $this->userRepository->upgradePassword($user, 'new-hash');
        $this->assertSame('new-hash', $user->getPassword());
    }

}

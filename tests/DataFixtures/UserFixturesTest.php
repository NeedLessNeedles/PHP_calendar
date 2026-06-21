<?php

/**
 * Tests for UserFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserFixturesTest.
 */
class UserFixturesTest extends TestCase
{
    public function testLoadDataPersistsUsers(): void
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');
        $fixtures = new UserFixtures($hasher);
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->exactly(4))
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $manager->expects($this->once())->method('flush');
        $fixtures->load($manager);

        $this->assertTrue(true);
    }

    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], UserFixtures::getGroups());
    }
}

<?php

/**
 * Tests for TagFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\TagFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class TagFixturesTest.
 */
class TagFixturesTest extends TestCase
{
    public function testLoadDataPersistsTags(): void
    {
        $fixtures = new TagFixtures();
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->exactly(9))->method('persist');
        $manager->expects($this->once())->method('flush');

        $fixtures->load($manager);
        $this->assertTrue(true);
    }

    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], TagFixtures::getGroups());
    }
}

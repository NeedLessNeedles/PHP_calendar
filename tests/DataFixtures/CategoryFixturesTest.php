<?php

/**
 * Tests for CategoryFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class categoryFixturesTest.
 */
class CategoryFixturesTest extends TestCase
{
    public function testLoadDataPersistsCategories(): void
    {
        $fixtures = new CategoryFixtures();
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->exactly(5))->method('persist');
        $manager->expects($this->once())->method('flush');
        $fixtures->load($manager);

        $this->assertTrue(true);
    }

    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], CategoryFixtures::getGroups());
    }
}

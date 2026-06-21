<?php

/**
 * Tests for AppFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AppFixturesTest.
 */
class AppFixturesTest extends TestCase
{
    public function testLoadDoesNotCrash(): void
    {
        $fixtures = new AppFixtures();
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())->method('flush');
        $fixtures->load($manager);

        $this->assertTrue(true);
    }
}

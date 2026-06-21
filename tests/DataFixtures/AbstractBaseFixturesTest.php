<?php

/**
 * Tests for AbstractBaseFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\AbstractBaseFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractBaseFixturesTest.
 */
class AbstractBaseFixturesTest extends TestCase
{
    public function testCreateManyThrows(): void
    {
        $fixture = new class extends AbstractBaseFixtures {
            protected function loadData(): void {}
        };
        $manager = $this->createMock(ObjectManager::class);

        $fixture->load($manager);
        $this->expectException(\LogicException::class);
        $ref = new \ReflectionMethod($fixture, 'createMany');
        $ref->setAccessible(true);

        $ref->invoke($fixture, 1, 'test', fn () => null);
    }

    public function testGetRandomReferenceThrows(): void
    {
        $fixture = new class extends AbstractBaseFixtures {
            protected function loadData(): void {}
        };

        $manager = $this->createMock(ObjectManager::class);
        $fixture->load($manager);
        $this->expectException(\InvalidArgumentException::class);

        $ref = new \ReflectionMethod($fixture, 'getRandomReference');
        //$ref->setAccessible(true);

        $ref->invoke($fixture, 'nonexistent', \stdClass::class);
    }
}

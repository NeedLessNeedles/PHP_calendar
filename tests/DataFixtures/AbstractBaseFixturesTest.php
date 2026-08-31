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
    /**
     * Test for crating many throws, whatever that means.
     */
    public function testCreateManyThrows(): void
    {
        $fixture = new class extends AbstractBaseFixtures
        {
            protected function loadData(): void
            {
            }
        };
        $manager = $this->createStub(ObjectManager::class);

        $fixture->load($manager);
        $this->expectException(\LogicException::class);
        $ref = new \ReflectionMethod($fixture, 'createMany');

        $ref->invoke($fixture, 1, 'test', fn () => null);
    }

    /**
     * Test get random reference throws.
     */
    public function testGetRandomReferenceThrows(): void
    {
        $fixture = new class extends AbstractBaseFixtures
        {
            protected function loadData(): void
            {
            }
        };
        $manager = $this->createStub(ObjectManager::class);
        $fixture->load($manager);
        $this->expectException(\InvalidArgumentException::class);

        $ref = new \ReflectionMethod($fixture, 'getRandomReference');

        $ref->invoke($fixture, 'nonexistent', \stdClass::class);
    }
}

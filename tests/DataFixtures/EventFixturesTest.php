<?php

/**
 * Tests for EventFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\EventFixtures;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class EventFixturesTest.
 */
class EventFixturesTest extends TestCase
{
    public function testLoadDataCreatesEvents(): void
    {
        $fixtures = new EventFixtures();
        $categoryRepo = $this->createMock(ObjectRepository::class);
        $tagRepo = $this->createMock(ObjectRepository::class);
        $categoryRepo->method('findAll')->willReturn([(new Category())->setTitle('Test'),]);

        $tagRepo->method('findAll')->willReturn([
            new Tag(),
            new Tag(),
        ]);
        $manager = $this->createMock(ObjectManager::class);

        $manager->method('getRepository')
            ->willReturnMap([
                [Category::class, $categoryRepo],
                [Tag::class, $tagRepo],
            ]);

        $manager->expects($this->exactly(20))->method('persist');
        $manager->expects($this->once())->method('flush');

        $fixtures->load($manager);
        $this->assertTrue(true);
    }

    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], EventFixtures::getGroups());
    }
}

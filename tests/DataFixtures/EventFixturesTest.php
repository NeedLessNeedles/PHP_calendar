<?php

/**
 * Tests for EventFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\EventFixtures;
use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\TagFixtures;
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
    /**
     * Test loading data.
     */
    public function testLoadDataCreatesEvents(): void
    {
        $fixtures = new EventFixtures();
        $categoryRepo = $this->createStub(ObjectRepository::class);
        $tagRepo = $this->createStub(ObjectRepository::class);
        $category = new Category();
        $category->setTitle('Test');

        $categoryRepo->method('findAll')->willReturn([$category]);

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

        $manager->expects($this->exactly(25))->method('persist');
        $manager->expects($this->once())->method('flush');

        $fixtures->load($manager);
        // $this->assertTrue(true);
    }

    /**
     * Test if fixtures group is set to 'main'.
     */
    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], EventFixtures::getGroups());
    }

    /**
     * Test fixture dependencies.
     */
    public function testFixtureDependencies(): void
    {
        $fixtures = new EventFixtures();

        $this->assertSame(
            [
                CategoryFixtures::class,
                TagFixtures::class,
            ],
            $fixtures->getDependencies()
        );
    }
}

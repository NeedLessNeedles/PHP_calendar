<?php

/**
 * Tests for Category entity.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryTest.
 */
class CategoryTest extends TestCase
{
    public function testConstructor(): void
    {
        $category = new Category();

        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getUpdatedAt());
    }

    public function testTitle(): void
    {
        $category = new Category();
        $category->setTitle('Technology');

        $this->assertEquals('Technology', $category->getTitle());
    }

    public function testSetCreatedAt(): void
    {
        $category = new Category();
        $date = new \DateTimeImmutable('2025-01-01 10:00:00');
        $category->setCreatedAt($date);

        $this->assertEquals($date, $category->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $category = new Category();
        $date = new \DateTimeImmutable('2025-01-02 10:00:00');
        $category->setUpdatedAt($date);

        $this->assertEquals($date, $category->getUpdatedAt());
    }

    public function testToString(): void
    {
        $category = new Category();
        $category->setTitle('Music');

        $this->assertEquals('Music', (string) $category);
    }

}

<?php

/**
 * Tests for Event entity.
 */

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Tag;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

/**
 * Class EventTest.
 */
class EventTest extends TestCase
{
    /**
     * Test set() and get() for Title column.
     */
    public function testTitle(): void
    {
        $event = new Event();
        $event->setTitle('Event title');

        $this->assertEquals('Event title', $event->getTitle());
    }

    /**
     * Test set() and get() for Description column.
     */
    public function testDescription(): void
    {
        $event = new Event();
        $event->setDescription('Event description');

        $this->assertEquals('Event description', $event->getDescription());
    }

    /**
     * Tests if Description column can be null.
     */
    public function testDescriptionCanBeNull(): void
    {
        $event = new Event();
        $event->setDescription(null);

        $this->assertNull($event->getDescription());
    }

    /**
     * Test set() and get() for Location column.
     */
    public function testLocation(): void
    {
        $event = new Event();
        $event->setLocation('Event location');

        $this->assertEquals('Event location', $event->getLocation());
    }

    /**
     * Tests if Location column can be null.
     */
    public function testLocationCanBeNull(): void
    {
        $event = new Event();
        $event->setLocation(null);

        $this->assertNull($event->getLocation());
    }

    /**
     * Test set() and get() for StartDate column.
     */
    public function testStartDate(): void
    {
        $event = new Event();
        $date = new \DateTime('2025-01-01 12:00');
        $event->setStartDate($date);

        $this->assertEquals($date, $event->getStartDate());
    }

    /**
     * Test set() and get() for EndDate column.
     */
    public function testEndDate(): void
    {
        $event = new Event();
        $date = new \DateTime('2025-01-01 12:00');
        $event->setEndDate($date);

        $this->assertSame($date, $event->getEndDate());
    }

    /**
     * Tests if EndDate column can be null.
     */
    public function testEndDateCanBeNull(): void
    {
        $event = new Event();

        $event->setEndDate(null);

        $this->assertNull($event->getEndDate());
    }

    /**
     * Test set() and get() for Owner column.
     */
    public function testOwner(): void
    {
        $event = new Event();
        $user = new User();
        $event->setOwner($user);

        $this->assertEquals($user, $event->getOwner());
    }

    /**
     * Tests if Owner column can be null.
     */
    public function testOwnerCanBeNull(): void
    {
        $event = new Event();
        $event->setOwner(null);

        $this->assertNull($event->getOwner());
    }

    /**
     * Test set() and get() for Status column.
     */
    public function testStatus(): void
    {
        $event = new Event();
        $event->setStatus('approved');

        $this->assertEquals('approved', $event->getStatus());
    }

    public function testCategory(): void
    {
        $event = new Event();
        $category = new Category();
        $category->setTitle('Technology');
        $event->setCategory($category);

        $this->assertEquals($category, $event->getCategory());
    }

    public function testTagsInitialized(): void
    {
        $event = new Event();

        $this->assertCount(0, $event->getTags());
    }

    public function testAddTag(): void
    {
        $event = new Event();
        $tag = new Tag();
        $tag->setTitle('online');
        $event->addTag($tag);

        $this->assertCount(1, $event->getTags());
        $this->assertTrue($event->getTags()->contains($tag));
    }

    public function testTagsDoesntDuplicate(): void
    {
        $event = new Event();
        $tag = new Tag();
        $event->addTag($tag);
        $event->addTag($tag);

        $this->assertCount(1, $event->getTags());
    }

    public function testRemoveTag(): void
    {
        $event = new Event();
        $tag = new Tag();
        $event->addTag($tag);

        $this->assertCount(1, $event->getTags());
        $event->removeTag($tag);
        $this->assertCount(0, $event->getTags());
    }
}

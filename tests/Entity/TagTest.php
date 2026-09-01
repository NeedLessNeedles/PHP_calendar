<?php

/**
 * Tests for Tag entity.
 */

namespace App\Tests\Entity;

use App\Entity\Tag;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

/**
 * Class TagTest.
 */
class TagTest extends TestCase
{
    /**
     * Test constructor.
     */
    public function testConstructor(): void
    {
        $tag = new Tag();

        $this->assertCount(0, $tag->getEvents());
    }

    /**
     * Test default values.
     */
    public function testDefaultValues(): void
    {
        $tag = new Tag();

        $this->assertNull($tag->getId());
        $this->assertNull($tag->getTitle());
        $this->assertCount(0, $tag->getEvents());
    }

    /**
     * Test get() and set() for Title column.
     */
    public function testTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('online');

        $this->assertEquals('online', $tag->getTitle());
    }

    /**
     * Test if tag can be added to the event.
     */
    public function testAddEvent(): void
    {
        $tag = new Tag();
        $event = new Event();

        $result = $tag->addEvent($event);

        $this->assertSame($tag, $result);

        $this->assertCount(1, $tag->getEvents());
        $this->assertTrue(
            $tag->getEvents()->contains($event)
        );

        $this->assertTrue(
            $event->getTags()->contains($tag)
        );
    }

    /**
     * Test if tag cannot be added to the event twice.
     */
    public function testTagToEventDoesntDuplicate(): void
    {
        $tag = new Tag();
        $event = new Event();

        $tag->addEvent($event);
        $tag->addEvent($event);

        $this->assertCount(1, $tag->getEvents());

        $this->assertTrue(
            $tag->getEvents()->contains($event)
        );
    }

    /**
     * Test if tag can be removed from the event.
     */
    public function testRemoveEvent(): void
    {
        $tag = new Tag();
        $event = new Event();

        $tag->addEvent($event);
        $this->assertCount(1, $tag->getEvents());
        $result = $tag->removeEvent($event);
        $this->assertSame($tag, $result);
        $this->assertCount(0, $tag->getEvents());

        $this->assertFalse(
            $tag->getEvents()->contains($event)
        );
        $this->assertFalse(
            $event->getTags()->contains($tag)
        );
    }
}

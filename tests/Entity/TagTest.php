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
    public function testConstructor(): void
    {
        $tag = new Tag();

        $this->assertCount(0, $tag->getEvents());
    }

    public function testTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('online');

        $this->assertEquals('online', $tag->getTitle());
    }

    public function testAddTagToEvent(): void
    {
        $tag = new Tag();
        $event = new Event();
        $tag->addEvent($event);

        $this->assertCount(1, $tag->getEvents());
        $this->assertTrue($tag->getEvents()->contains($event));
    }

    public function testTagToEventDoesntDuplicate(): void
    {
        $tag = new Tag();
        $event = new Event();
        $tag->addEvent($event);
        $tag->addEvent($event);

        $this->assertCount(1, $tag->getEvents());
    }

    public function testRemoveEvent(): void
    {
        $tag = new Tag();
        $event = new Event();
        $tag->addEvent($event);

        $this->assertCount(1, $tag->getEvents());
        $tag->removeEvent($event);
        $this->assertCount(0, $tag->getEvents());
    }

}

<?php

/**
 * Tests for EventFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\EventFixtures;
use PHPUnit\Framework\TestCase;

/**
 * Class EventFixturesTest.
 */
class EventFixturesTest extends TestCase
{
    /**
     * Test if fixtures group is set to 'main'.
     */
    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], EventFixtures::getGroups());
    }
}

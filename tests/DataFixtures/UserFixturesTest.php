<?php

/**
 * Tests for UserFixtures.
 */

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use PHPUnit\Framework\TestCase;

/**
 * Class UserFixturesTest.
 */
class UserFixturesTest extends TestCase
{
    /**
     * Test if group is main.
     */
    public function testFixtureGroupIsMain(): void
    {
        $this->assertSame(['main'], UserFixtures::getGroups());
    }
}

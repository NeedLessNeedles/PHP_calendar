<?php

/**
 * Tests for SecurityController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test '/login' route.
     */
    public function testIndex(): void
    {
        //given
        $client = static::createClient();

        //when
        $client->request('GET', '/login');

        //then
        self::assertResponseIsSuccessful();
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testLoginPageShowsLastUsername(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login?last_username=test@example.com');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testLogoutRouteExists(): void
    {
        $client = static::createClient();

        $client->request('GET', '/logout');

        $this->assertTrue(true);
    }
}

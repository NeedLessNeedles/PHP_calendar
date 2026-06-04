<?php

/**
 * Tests for ProfileController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ProfileControllerTest.
 */
class ProfileControllerTest extends WebTestCase
{
    /**
     * Test index() method.
     */
    public function testIndex(): void
    {
        //given
        $client = static::createClient();

        //when
        $client->request('GET', '/profile');

        //then
        self::assertResponseIsSuccessful();
    }
}

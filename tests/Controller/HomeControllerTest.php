<?php

/**
 * Tests for HomeController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomeControllerTest.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test '/home' route.
     */
    public function testIndex(): void
    {
        //given
        $client = static::createClient();

        //when
        $client->request('GET', '/home');

        //then
        self::assertResponseIsSuccessful();
    }

    public function testHomePageAsAnonymousUser(): void
    {
        $client = static::createClient();

        $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
        $this->assertRouteSame('app_home');
    }

    public function testHomeRedirectsToEventsForLoggedUser(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')
            ->getRepository(\App\Entity\User::class);

        $testUser = $userRepository->findOneBy(['roles' => ['ROLE_USER']]);

        $client->loginUser($testUser);

        $client->request('GET', '/home');

        $this->assertResponseRedirects('/event');
    }
}

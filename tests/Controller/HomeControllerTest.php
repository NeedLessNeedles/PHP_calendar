<?php

/**
 * Tests for HomeController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomeControllerTest.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Anonymous user can view home page.
     */
    public function testIndexAsAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Logged user is redirected.
     */
    public function testIndexAsLoggedUser(): void
    {
        $client = static::createClient();
        $user = $this->getUser($client);
        $client->loginUser($user);
        $client->request('GET', '/home');

        $this->assertResponseRedirects();
    }

    /**
     * Get user.
     *
     * @param <string> $client Client
     *
     * @return User user
     */
    private function getUser($client): User
    {
        $user = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy([
                'email' => 'user.first@gmail.com',
            ]);

        $this->assertInstanceOf(User::class, $user);

        return $user;
    }
}

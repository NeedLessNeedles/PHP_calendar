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
     * Tests if nonymous user can view the home page.
     */
    public function testIndexAsAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Tests if logged user is redirected.
     */
    public function testIndexAsLoggedUser(): void
    {
        $client = static::createClient();

        $manager = static::getContainer()
            ->get(EntityManagerInterface::class);

        $user = $this->createUser();

        $manager->persist($user);
        $manager->flush();

        $client->loginUser($user);

        $client->request('GET', '/home');

        self::assertResponseRedirects();
    }

    /**
     * Helper for creating regular user.
     *
     * @return User admin user
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('home-test-'.uniqid('', true).'@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        return $user;
    }
}

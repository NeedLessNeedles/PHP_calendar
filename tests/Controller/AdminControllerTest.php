<?php

/**
 * Tests for AdminController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AdminControllerTest.
 */
class AdminControllerTest extends WebTestCase
{
    private function getUser($client, string $email): User
    {
        return $client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    private function getEvent($client, string $status): ?Event
    {
        return $client->getContainer()
            ->get('doctrine')
            ->getRepository(Event::class)
            ->findOneBy(['status' => $status]);
    }

    public function testIndexRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects();
    }

    public function testIndexAsAdmin(): void
    {
        $client = static::createClient();
        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $client->loginUser($admin);
        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testUsersList(): void
    {
        $client = static::createClient();
        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $client->loginUser($admin);
        $client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
    }

    public function testBlockUser(): void
    {
        $client = static::createClient();
        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $user = $this->getUser($client, 'user.first@gmail.com');
        $client->loginUser($admin);
        $client->request('POST', '/admin/users/' . $user->getId() . '/block');

        $this->assertResponseRedirects('/admin/users');
    }

    public function testRequestsPage(): void
    {
        $client = static::createClient();
        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $client->loginUser($admin);
        $client->request('GET', '/admin/requests');

        $this->assertResponseIsSuccessful();
    }

    public function testApproveEvent(): void
    {
        $client = static::createClient();
        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $event = $this->getEvent($client, 'pending');
        $this->assertNotNull($event);
        $client->loginUser($admin);
        $client->request('POST', '/admin/requests/' . $event->getId() . '/approve');

        $this->assertResponseRedirects('/admin/requests');
    }

    public function testRejectEvent(): void
    {
        $client = static::createClient();
        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $event = $this->getEvent($client, 'pending');
        $this->assertNotNull($event);
        $client->loginUser($admin);
        $client->request('POST', '/admin/requests/' . $event->getId() . '/reject');

        $this->assertResponseRedirects('/admin/requests');
    }
}

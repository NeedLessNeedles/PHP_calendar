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

    private function loginUser(): User
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneBy([
            'email' => 'user.first@gmail.com',
        ]);

        $client = static::createClient();
        $client->loginUser($user);

        return $user;
    }

    public function testProfileIndexGet(): void
    {
        $client = static::createClient();
        $this->loginUser();

        $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testProfileEmailUpdate(): void
    {
        $client = static::createClient();
        $user = $this->loginUser();

        $crawler = $client->request('GET', '/profile');

        $form = $crawler->selectButton('Zapisz')->form([
            'profile_email[email]' => 'newmail@example.com',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $updated = $em->getRepository(User::class)->find($user->getId());

        $this->assertSame('newmail@example.com', $updated->getEmail());
    }

    public function testProfilePasswordChange(): void
    {
        $client = static::createClient();
        $user = $this->loginUser();

        $crawler = $client->request('GET', '/profile');

        $form = $crawler->selectButton('Zapisz')->form([
            'change_password[newPassword]' => 'newpassword123',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $updated = $em->getRepository(User::class)->find($user->getId());

        $this->assertTrue(
            $passwordHasher->isPasswordValid($updated, 'newpassword123')
        );
    }

    public function testProfileEditPage(): void
    {
        $client = static::createClient();
        $this->loginUser();

        $client->request('GET', '/profile/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'profile');
    }
}

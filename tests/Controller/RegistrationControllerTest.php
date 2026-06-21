<?php

/**
 * Tests for RegistrationController.
 */

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationControllerTest.
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Test '/register' route.
     */
    public function testIndex(): void
    {
        //given
        $client = static::createClient();

        //when
        $client->request('GET', '/register');

        //then
        self::assertResponseIsSuccessful();
    }

    /**
     * Test Setup method.
     */
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Ensure we have a clean database
        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();
    }

    public function testRegister(): void
    {
        // Register a new user
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Register');

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'me@example.com',
            'registration_form[plainPassword]' => 'password',
            'registration_form[agreeTerms]' => true,
        ]);

        // Ensure the response redirects after submitting the form, the user exists, and is not verified
        // self::assertResponseRedirects('/'); @TODO: set the appropriate path that the user is redirected to.
        self::assertCount(1, $this->userRepository->findAll());
    }

    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testRegisterNewUser(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'new.user@example.com',
            'registration_form[plainPassword]' => 'password123',
            'registration_form[agreeTerms]' => true,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy([
            'email' => 'new.user@example.com',
        ]);

        $this->assertNotNull($user);
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testPasswordIsHashed(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $client->submit($crawler->selectButton('Register')->form([
            'registration_form[email]' => 'hash.test@example.com',
            'registration_form[plainPassword]' => 'password123',
            'registration_form[agreeTerms]' => true,
        ]));

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy([
            'email' => 'hash.test@example.com',
        ]);

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->assertTrue(
            $passwordHasher->isPasswordValid($user, 'password123')
        );
    }
}

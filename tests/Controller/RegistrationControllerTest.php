<?php

/**
 * Tests for RegistrationController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\RegistrationServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationControllerTest.
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Browser client.
     */
    private KernelBrowser $client;

    /**
     * Registration page can be displayed.
     */
    public function testRegisterPageLoads(): void
    {
        $crawler = $this->client->request(
            'GET',
            '/register'
        );

        self::assertResponseIsSuccessful();

        self::assertGreaterThanOrEqual(
            1,
            $crawler->filter('form')->count()
        );
    }

    /**
     * Invalid registration form is rendered again.
     */
    public function testRegisterRejectsInvalidForm(): void
    {
        $service = $this->mockRegistrationService();

        $service
            ->expects($this->never())
            ->method('registerUser');

        $this->client->request(
            'POST',
            '/register',
            [
                'registration_form' => [
                    'email' => 'invalid-email',
                    'plainPassword' => '123',
                    'agreeTerms' => false,
                ],
            ]
        );

        self::assertResponseStatusCodeSame(422);

        self::assertSelectorExists('form');
    }

    /**
     * Valid user registration calls registration service.
     */
    public function testRegisterValidUser(): void
    {
        $service = $this->mockRegistrationService();

        $service
            ->expects($this->once())
            ->method('registerUser')
            ->with(
                $this->callback(
                    static function (User $user): bool {
                        return 'new.user@example.com'
                            === $user->getEmail();
                    }
                ),
                'password123'
            );

        $crawler = $this->client->request(
            'GET',
            '/register'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form')
            ->form();

        $form['registration_form[email]']
            = 'new.user@example.com';

        $form['registration_form[plainPassword]']
            = 'password123';

        $form['registration_form[agreeTerms]']
            = true;

        $this->client->submit($form);

        self::assertResponseRedirects();
    }

    /**
     * Valid registration passes submitted password to service.
     */
    public function testRegisterPassesPlainPasswordToService(): void
    {
        $service = $this->mockRegistrationService();

        $service
            ->expects($this->once())
            ->method('registerUser')
            ->with(
                $this->isInstanceOf(User::class),
                'another-password'
            );

        $crawler = $this->client->request(
            'GET',
            '/register'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form')
            ->form();

        $form['registration_form[email]']
            = 'password.test@example.com';

        $form['registration_form[plainPassword]']
            = 'another-password';

        $form['registration_form[agreeTerms]']
            = true;

        $this->client->submit($form);

        self::assertResponseRedirects();
    }

    /**
     * Create browser client.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * Get registration service mock.
     *
     * @return RegistrationServiceInterface Registration service interface
     */
    private function mockRegistrationService(): RegistrationServiceInterface
    {
        $service = $this->createMock(
            RegistrationServiceInterface::class
        );

        static::getContainer()->set(
            RegistrationServiceInterface::class,
            $service
        );

        return $service;
    }
}

<?php

/**
 * Tests for RegistrationController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\RegistrationServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @return RegistrationServiceInterface&MockObject
     */
    private function mockRegistrationService(): RegistrationServiceInterface&MockObject
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

        self::assertResponseIsSuccessful();

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

        self::assertResponseIsRedirect();
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

        self::assertResponseIsRedirect();
    }
}

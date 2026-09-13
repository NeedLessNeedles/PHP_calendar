<?php

/**
 * Tests for RegistrationController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Security\CustomAuthenticator;
use App\Service\RegistrationServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

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
            ->method('canBeEmpty')
            ->willReturn(true);

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
                ],
            ]
        );

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('form');
    }

    /**
     * Valid registration is handled successfully.
     */
    public function testRegisterValidUser(): void
    {
        $email = 'registration-test-'.uniqid('', true).'@example.com';

        $crawler = $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form[name="registration_form"]')
            ->form();

        $form['registration_form[email]'] = $email;
        $form['registration_form[plainPassword]'] = 'password123';

        $this->client->submit($form);

        self::assertResponseRedirects('/event');
    }

    /**
     * Registration with empty data is rejected.
     */
    public function testRegisterRejectsEmptyData(): void
    {
        $service = $this->mockRegistrationService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('registerUser');

        $this->client->request(
            'POST',
            '/register',
            [
                'registration_form' => [
                    'email' => 'test@example.com',
                    'plainPassword' => 'password123',
                ],
            ]
        );

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('form');
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

<?php

/**
 * Tests for SecurityController.
 */

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Service\SecurityServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Browser client.
     */
    private KernelBrowser $client;

    /**
     * Login page can be displayed.
     */
    public function testLoginPageLoads(): void
    {
        $service = $this->mockSecurityService();

        $service
            ->expects($this->once())
            ->method('getLoginData')
            ->willReturn([
                'last_username' => '',
                'error' => null,
            ]);

        $this->client->request(
            'GET',
            '/login'
        );

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('form');
    }

    /**
     * Login action renders data returned by security service.
     */
    public function testLoginUsesSecurityService(): void
    {
        $service = $this->mockSecurityService();

        $service
            ->expects($this->once())
            ->method('getLoginData')
            ->willReturn([
                'last_username' => 'test@example.com',
                'error' => null,
            ]);

        $this->client->request(
            'GET',
            '/login'
        );

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('input');
    }

    /**
     * Logout action throws logic exception when called directly.
     */
    public function testLogoutThrowsLogicException(): void
    {
        $service = $this->createMock(
            SecurityServiceInterface::class
        );

        $controller = new SecurityController($service);

        $this->expectException(
            \LogicException::class
        );

        $this->expectExceptionMessage(
            'This method can be blank'
        );

        $controller->logout();
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
     * Get security service mock.
     *
     * @return SecurityServiceInterface&MockObject Security service interface and Mock object
     */
    private function mockSecurityService(): SecurityServiceInterface&MockObject
    {
        $service = $this->createMock(
            SecurityServiceInterface::class
        );

        static::getContainer()->set(
            SecurityServiceInterface::class,
            $service
        );

        return $service;
    }
}

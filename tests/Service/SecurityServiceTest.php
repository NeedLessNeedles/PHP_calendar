<?php

/**
 * Tests for SecurityService.
 */

namespace App\Tests\Service;

use App\Service\SecurityService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class SecurityServiceTest.
 */
class SecurityServiceTest extends TestCase
{
    /**
     * Test login data.
     */
    public function testGetLoginData(): void
    {
        $authenticationUtils = $this->createMock(AuthenticationUtils::class);

        $authenticationUtils
            ->expects($this->once())
            ->method('getLastUsername')
            ->willReturn('test@example.com');

        $authenticationError = new AuthenticationException('Authentication failed');

        $authenticationUtils
            ->expects($this->once())
            ->method('getLastAuthenticationError')
            ->willReturn($authenticationError);

        $service = new SecurityService($authenticationUtils);

        $result = $service->getLoginData();

        $this->assertSame(
            [
                'last_username' => 'test@example.com',
                'error' => $authenticationError,
            ],
            $result
        );
    }
}

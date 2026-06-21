<?php

/**
 * Tests for CustomAuthenticator.
 */

namespace App\Tests\Security;

use App\Security\CustomAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Class CustomAuthenticatorTest.
 */
class CustomAuthenticatorTest extends TestCase
{
    public function testGetLoginUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('app_login')
            ->willReturn('/login');

        $authenticator = new CustomAuthenticator($urlGenerator);

        $ref = new \ReflectionClass($authenticator);
        $method = $ref->getMethod('getLoginUrl');
        //$method->setAccessible(true);
        $request = new Request();

        $this->assertSame('/login', $method->invoke($authenticator, $request));
    }

    public function testOnAuthenticationSuccessRedirectsToEventIndex(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('app_event_index')
            ->willReturn('/events');

        $authenticator = new CustomAuthenticator($urlGenerator);
        $token = $this->createMock(TokenInterface::class);
        $request = new Request();

        $ref = new \ReflectionClass($authenticator);
        $method = $ref->getMethod('onAuthenticationSuccess');
        $method->setAccessible(true);
        $response = $method->invoke($authenticator, $request, $token, 'main');

        $this->assertSame(302, $response->getStatusCode());
    }
}

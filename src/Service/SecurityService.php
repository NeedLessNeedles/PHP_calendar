<?php

/**
 * Security service.
 */

namespace App\Service;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityService.
 */
class SecurityService implements SecurityServiceInterface
{
    /**
     * Constructor.
     *
     * @param AuthenticationUtils $authenticationUtils Authentication utils
     */
    public function __construct(private readonly AuthenticationUtils $authenticationUtils)
    {
    }

    /**
     * Get login data.
     *
     * @return array<string, mixed> Login data
     */
    public function getLoginData(): array
    {
        return [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ];
    }
}

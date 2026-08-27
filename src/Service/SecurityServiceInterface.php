<?php

/**
 * Security service interface.
 */

namespace App\Service;

/**
 * Interface SecurityServiceInterface.
 */
interface SecurityServiceInterface
{
    /**
     * Get login data.
     *
     * @return array<string, mixed> Login data
     */
    public function getLoginData(): array;
}

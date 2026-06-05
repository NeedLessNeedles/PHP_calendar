<?php

/**
 * Tests for User entity.
 */

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest.
 */
class UserTest extends TestCase
{
    /**
     * Test set() and get() for Email column.
     */
    public function testEmail(): void
    {
        $user = new User();
        $user->setEmail('user.email@service.com');

        $this->assertEquals('Event title', $user->getEmail());
    }

    /**
     * Test set() and get() for Roles column.
     */
    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $this->assertEquals('ROLE_USER', $user->getRoles());
    }

    /**
     * Test set() and get() for Password column.
     */
    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('examplePASSWORD123');

        $this->assertEquals('examplePASSWORD123', $user->getPassword());
    }
}

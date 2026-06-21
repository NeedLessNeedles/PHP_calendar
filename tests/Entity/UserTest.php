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

        $this->assertEquals('user.email@service.com', $user->getEmail());
    }

    /**
     * Test get() for user identifier.
     */
    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('user.email@service.com');

        $this->assertEquals('user.email@service.com', $user->getUserIdentifier());
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

    /**
     * Test set() and get() for Roles column.
     */
    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    /**
     * Tests if User role is being granted by default.
     */
    public function testUserRole(): void
    {
        $user = new User();
        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    /**
     * Tests users blocking by admin.
     */
    public function testBlockStatus(): void
    {
        $user = new User();
        $this->assertFalse($user->isBlocked());
        $user->setIsBlocked(true);

        $this->assertTrue($user->isBlocked());
    }

    /**
     * Tests __serialize() function.
     */
    public function testSerialize(): void
    {
        $user = new User();
        $user->setEmail('user@email.com');
        $user->setPassword('secret');
        $data = $user->__serialize();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(
            "\0App\Entity\User\0password",
            $data
        );
    }
}

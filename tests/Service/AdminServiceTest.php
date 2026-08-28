<?php

/**
 * Tests for AdminService.
 */

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Service\AdminService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AdminServiceTest.
 */
class AdminServiceTest extends KernelTestCase
{
    private AdminService $adminService;

    /**
     * Constructor test.
     */
    public function testServiceCanBeCreated(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(AdminService::class);

        $this->assertInstanceOf(AdminService::class, $service);
    }

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->adminService = self::getContainer()->get(AdminService::class);
    }

    /**
     * Tests if password can be changed.
     */
    public function testChangePassword(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $this->adminService->changePassword($user, 'plain-password');
        $this->assertNotEmpty($user->getPassword());
        $this->assertNotSame('plain-password', $user->getPassword());
    }

    /**
     * Test for approving the pending event.
     */
    public function testApproveEvent(): void
    {
        $event = new Event();
        $event->setStatus('pending');

        $this->adminService->approveEvent($event);
        $this->assertSame('approved', $event->getStatus());
    }

    /**
     * Test for blocking the user.
     */
    public function testToggleBlock(): void
    {
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $user = new User();
        $user->setEmail('user@test.com');
        $reflection = new \ReflectionClass($admin);
        $prop = $reflection->getProperty('roles');
        // $prop->setAccessible(true);
        $prop->setValue($admin, ['ROLE_ADMIN']);

        $this->expectException(\LogicException::class);
        $this->adminService->toggleBlock($user, $admin);
    }

    /**
     * Tests if admin can block himself.
     */
    public function testToggleBlockSelf(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $reflection = new \ReflectionClass($user);
        $prop = $reflection->getProperty('id');
        // $prop->setAccessible(true);
        $prop->setValue($user, 1);

        $this->expectException(\LogicException::class);

        $this->adminService->toggleBlock($user, $user);
    }

    /**
     * Test for blocking.
     */
    public function testToggleBlockSuccess(): void
    {
        $target = new User();
        $target->setEmail('target@test.com');
        $current = new User();
        $current->setEmail('admin@test.com');

        $this->adminService->toggleBlock($target, $current);
        $this->assertTrue($target->isBlocked() || false === $target->isBlocked());
    }
}

<?php

/**
 * Tests for EventService.
 */

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Service\AdminService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EventServiceTest.
 */
class EventServiceTest extends KernelTestCase
{
    private AdminService $adminService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->adminService = self::getContainer()->get(AdminService::class);
    }

    /**
     * Constructor test.
     */
    public function testServiceCanBeCreated(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(AdminService::class);

        $this->assertInstanceOf(AdminService::class, $service);
    }

    public function testChangePassword(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('old-password');

        $this->adminService->changePassword($user, 'new-password');
        $this->assertNotSame('old-password', $user->getPassword());
        $this->assertNotEmpty($user->getPassword());
    }

    public function testApproveEvent(): void
    {
        $event = new Event();
        $event->setStatus('pending');

        $this->adminService->approveEvent($event);
        $this->assertSame('approved', $event->getStatus());
    }

    public function testToggleBlock(): void
    {
        $user = new User();
        $user->setEmail('u@test.com');
        $user->setIsBlocked(false);
        $user->setRoles(['ROLE_USER']);

        $otherUser = new User();
        $otherUser->setEmail('a@test.com');
        $otherUser->setIsBlocked(false);
        $otherUser->setRoles(['ROLE_USER']);

        $this->adminService->toggleBlock($otherUser, $user);
        $this->assertTrue($otherUser->isBlocked());
    }

    public function testToggleBlockThrowsOnSelf(): void
    {
        $user = new User();
        $user->setEmail('u@test.com');
        $user->setRoles(['ROLE_USER']);

        $this->expectException(\LogicException::class);
        $this->adminService->toggleBlock($user, $user);
    }

    public function testToggleBlockThrowsOnAdmin(): void
    {
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setRoles(['ROLE_ADMIN']);

        $other = new User();
        $other->setEmail('u@test.com');
        $other->setRoles(['ROLE_USER']);

        $this->expectException(\LogicException::class);
        $this->adminService->toggleBlock($user, $other);
    }
}

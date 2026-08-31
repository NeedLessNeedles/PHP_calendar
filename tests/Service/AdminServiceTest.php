<?php

/**
 * Tests for AdminService.
 */

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Service\AdminService;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
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

    /** * Test for approving the pending event. */
    public function testApproveEvent(): void
    {
        $event = new Event();

        $event->setStatus('pending');
        $event->setTitle('Test event');
        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->once())->method('save')->with($this->identicalTo($event));
        $adminService = new AdminService(self::getContainer()->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class), self::getContainer()->get(UserRepository::class), $eventRepository);
        $adminService->approveEvent($event);
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
        $reflection = new \ReflectionClass(User::class);
        $targetId = $reflection->getProperty('id');
        $targetId->setValue($target, 1);
        $currentId = $reflection->getProperty('id');
        $currentId->setValue($current, 2);
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('save')->with($this->identicalTo($target));
        $adminService = new AdminService(self::getContainer()->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class), $userRepository, self::getContainer()->get(EventRepository::class));
        $this->assertFalse($target->isBlocked());
        $adminService->toggleBlock($target, $current);
        $this->assertTrue($target->isBlocked());
    }
}

<?php

/**
 * Tests for AdminService.
 */

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\AdminService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminServiceTest.
 */
class AdminServiceTest extends TestCase
{
    private UserRepository $userRepository;

    private EventRepository $eventRepository;

    private UserPasswordHasherInterface $passwordHasher;

    private AdminService $service;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createStub(
            UserRepository::class
        );
        $this->eventRepository = $this->createStub(
            EventRepository::class
        );
        $this->passwordHasher = $this->createStub(
            UserPasswordHasherInterface::class
        );

        $this->service = new AdminService(
            $this->passwordHasher,
            $this->userRepository,
            $this->eventRepository
        );
    }

    /**
     * Tests if password can be changed.
     */
    public function testChangePassword(): void
    {
        $user = new User();

        $passwordHasher = $this->createMock(
            UserPasswordHasherInterface::class
        );

        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'plain-password')
            ->willReturn('hashed-password');

        $this->service = new AdminService(
            $passwordHasher,
            $this->userRepository,
            $this->eventRepository
        );

        $this->service->changePassword(
            $user,
            'plain-password'
        );

        $this->assertSame(
            'hashed-password',
            $user->getPassword()
        );
    }

    /**
     * Test for approving event.
     */
    public function testApproveEvent(): void
    {
        $event = new Event();
        $event->setStatus('pending');

        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->service = new AdminService(
            $this->passwordHasher,
            $this->userRepository,
            $eventRepository
        );

        $this->service->approveEvent($event);

        $this->assertSame(
            'approved',
            $event->getStatus()
        );
    }

    /**
     * Test for rejecting event.
     */
    public function testRejectEvent(): void
    {
        $event = new Event();

        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('delete')
            ->with($event);

        $this->service = new AdminService(
            $this->passwordHasher,
            $this->userRepository,
            $eventRepository
        );

        $this->service->rejectEvent($event);
    }

    /**
     * Test that user cannot block himself.
     */
    public function testToggleBlockThrowsWhenBlockingSelf(): void
    {
        $user = new User();

        $this->setUserId($user, 1);

        $this->expectException(
            \LogicException::class
        );

        $this->expectExceptionMessage(
            'You cannot block yourself.'
        );

        $this->service->toggleBlock(
            $user,
            $user
        );
    }

    /**
     * Test that user cannot block another admin.
     */
    public function testToggleBlockThrowsWhenTargetIsAdmin(): void
    {
        $targetUser = new User();
        $targetUser->setRoles(['ROLE_ADMIN']);

        $currentUser = new User();

        $this->setUserId($targetUser, 1);
        $this->setUserId($currentUser, 2);

        $this->expectException(
            \LogicException::class
        );

        $this->expectExceptionMessage(
            'You cannot block another admin.'
        );

        $this->service->toggleBlock(
            $targetUser,
            $currentUser
        );
    }

    /**
     * Test for blocking user.
     */
    public function testToggleBlockBlocksUser(): void
    {
        $targetUser = new User();
        $targetUser->setIsBlocked(false);

        $currentUser = new User();

        $this->setUserId($targetUser, 1);
        $this->setUserId($currentUser, 2);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('save')
            ->with($targetUser);

        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );

        $this->service->toggleBlock(
            $targetUser,
            $currentUser
        );

        $this->assertTrue(
            $targetUser->isBlocked()
        );
    }

    /**
     * Test for unblocking user.
     */
    public function testToggleBlockUnblocksUser(): void
    {
        $targetUser = new User();
        $targetUser->setIsBlocked(true);

        $currentUser = new User();

        $this->setUserId($targetUser, 1);
        $this->setUserId($currentUser, 2);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('save')
            ->with($targetUser);

        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );

        $this->service->toggleBlock(
            $targetUser,
            $currentUser
        );

        $this->assertFalse(
            $targetUser->isBlocked()
        );
    }

    /**
     * Test counting administrators.
     */
    public function testCountAdmins(): void
    {
        $adminOne = new User();
        $adminOne->setRoles([
            'ROLE_USER',
            'ROLE_ADMIN',
        ]);

        $user = new User();
        $user->setRoles([
            'ROLE_USER',
        ]);

        $adminTwo = new User();
        $adminTwo->setRoles([
            'ROLE_ADMIN',
        ]);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                $adminOne,
                $user,
                $adminTwo,
            ]);

        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );

        $this->assertSame(
            2,
            $this->service->countAdmins()
        );
    }

    /**
     * Test counting administrators when there are none.
     */
    public function testCountAdminsReturnsZero(): void
    {
        $userOne = new User();
        $userOne->setRoles(['ROLE_USER']);

        $userTwo = new User();
        $userTwo->setRoles(['ROLE_USER']);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                $userOne,
                $userTwo,
            ]);

        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );

        $this->assertSame(
            0,
            $this->service->countAdmins()
        );
    }

    /**
     * Test adding administrator role.
     */
    public function testToggleAdminRoleAddsAdminRole(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );

        $this->service->toggleAdminRole($user);

        $this->assertContains(
            'ROLE_ADMIN',
            $user->getRoles()
        );
    }

    /**
     * Test removing administrator role.
     */
    public function testToggleAdminRoleRemovesAdminRole(): void
    {
        $user = new User();
        $user->setRoles([
            'ROLE_USER',
            'ROLE_ADMIN',
        ]);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('countAdministrators')
            ->willReturn(2);

        $userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );

        $this->service->toggleAdminRole($user);

        $this->assertNotContains(
            'ROLE_ADMIN',
            $user->getRoles()
        );

        $this->assertSame(
            ['ROLE_USER'],
            $user->getRoles()
        );
    }

    /**
     * Test if the last administrator cannot lose admin role.
     */
    public function testToggleAdminRoleThrowsForLastAdmin(): void
    {
        $user = new User();
        $user->setRoles([
            'ROLE_ADMIN',
        ]);
        $userRepository = $this->createMock(
            UserRepository::class
        );
        $userRepository
            ->expects($this->once())
            ->method('countAdministrators')
            ->willReturn(1);
        $userRepository
            ->expects($this->never())
            ->method('save');
        $this->service = new AdminService(
            $this->passwordHasher,
            $userRepository,
            $this->eventRepository
        );
        $this->expectException(
            \LogicException::class
        );
        $this->expectExceptionMessage(
            'Cannot remove administrator role from the last administrator.'
        );

        $this->service->toggleAdminRole($user);
    }

    /**
     * Set user ID for tests.
     *
     * @param User $user User
     * @param int  $id   ID
     */
    private function setUserId(User $user, int $id): void
    {
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');

        $property->setValue($user, $id);
    }
}

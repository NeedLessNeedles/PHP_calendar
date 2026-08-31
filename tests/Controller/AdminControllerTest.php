<?php

/**
 * Tests for AdminController.
 */

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Service\AdminServiceInterface;
use App\Service\EventServiceInterface;
use App\Service\ProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AdminControllerTest.
 */
class AdminControllerTest extends WebTestCase
{
    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
    }

    private function getAdmin($client): User
    {
        $admin = $this->getEntityManager($client)
            ->getRepository(User::class)
            ->findOneBy([
                'email' => 'admin.first@gmail.com',
            ]);

        $this->assertInstanceOf(User::class, $admin);

        return $admin;
    }

    private function getUser($client): User
    {
        $user = $this->getEntityManager($client)
            ->getRepository(User::class)
            ->findOneBy([
                'email' => 'user.first@gmail.com',
            ]);

        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    private function getPendingEvent($client): Event
    {
        $event = $this->getEntityManager($client)
            ->getRepository(Event::class)
            ->findOneBy([
                'status' => 'pending',
            ]);

        $this->assertInstanceOf(Event::class, $event);

        return $event;
    }

    /**
     * Index requires authentication.
     */
    public function testIndexRequiresLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        $this->assertResponseRedirects();
    }

    /**
     * Administrator can view index.
     */
    public function testIndexAsAdmin(): void
    {
        $client = static::createClient();

        $client->loginUser($this->getAdmin());

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    /**
     * Users list uses profile service.
     */
    public function testUsersList(): void
    {
        $client = static::createClient();

        $pagination = $this->createMock(
            PaginationInterface::class
        );

        $this->profileService
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(1)
            ->willReturn($pagination);

        $client->loginUser($this->getAdmin());

        $client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Users list passes requested page.
     */
    public function testUsersListWithPage(): void
    {
        $client = static::createClient();

        $pagination = $this->createMock(
            PaginationInterface::class
        );

        $this->profileService
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(3)
            ->willReturn($pagination);

        $client->loginUser($this->getAdmin());

        $client->request(
            'GET',
            '/admin/users?page=3'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Administrator can view user edit page.
     */
    public function testEditUser(): void
    {
        $client = static::createClient();

        $admin = $this->getAdmin($client);
        $user = $this->getUser($client);

        $client->loginUser($admin);

        $client->request(
            'GET',
            '/admin/users/'.$user->getId().'/edit'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Change email page can be displayed.
     */
    public function testChangeEmailGet(): void
    {
        $client = static::createClient();

        $client->loginUser($this->getAdmin());

        $user = $this->getUser();

        $client->request(
            'GET',
            '/admin/users/'.$user->getId().'/change_email'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Empty email is rejected.
     */
    public function testChangeEmailRejectsEmptyEmail(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $this->profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with('')
            ->willReturn(false);

        $this->profileService
            ->expects($this->never())
            ->method('isEmailUnique');

        $this->profileService
            ->expects($this->never())
            ->method('saveEmail');

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/change_email',
            [
                'change_email' => [
                    'email' => '',
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/admin/users/'.$user->getId().'/change_email'
        );
    }

    /**
     * Duplicate email is rejected.
     */
    public function testChangeEmailRejectsDuplicateEmail(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $this->profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with('duplicate@example.com')
            ->willReturn(true);

        $this->profileService
            ->expects($this->once())
            ->method('isEmailUnique')
            ->with(
                $this->identicalTo($user),
                'duplicate@example.com'
            )
            ->willReturn(false);

        $this->profileService
            ->expects($this->never())
            ->method('saveEmail');

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/change_email',
            [
                'change_email' => [
                    'email' => 'duplicate@example.com',
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/admin/users/'.$user->getId().'/change_email'
        );
    }

    /**
     * Valid email is saved.
     */
    public function testChangeEmailSavesValidEmail(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $email = 'new-email@example.com';

        $this->profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with($email)
            ->willReturn(true);

        $this->profileService
            ->expects($this->once())
            ->method('isEmailUnique')
            ->with(
                $this->identicalTo($user),
                $email
            )
            ->willReturn(true);

        $this->profileService
            ->expects($this->once())
            ->method('saveEmail')
            ->with(
                $this->identicalTo($user),
                $email
            );

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/change_email',
            [
                'change_email' => [
                    'email' => $email,
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/admin/users/'.$user->getId().'/edit'
        );
    }

    /**
     * Change password page can be displayed.
     */
    public function testChangePasswordGet(): void
    {
        $client = static::createClient();

        $client->loginUser($this->getAdmin());

        $user = $this->getUser();

        $client->request(
            'GET',
            '/admin/users/'.$user->getId().'/change_password'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Empty password is rejected.
     */
    public function testChangePasswordRejectsEmptyPassword(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $this->profileService
            ->expects($this->once())
            ->method('canPasswordBeEmpty')
            ->with('')
            ->willReturn(false);

        $this->profileService
            ->expects($this->never())
            ->method('isPasswordLongEnough');

        $this->profileService
            ->expects($this->never())
            ->method('savePassword');

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/change_password',
            [
                'admin_change_password' => [
                    'newPassword' => '',
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/admin/users/'.$user->getId().'/change_password'
        );
    }

    /**
     * Too short password is rejected.
     */
    public function testChangePasswordRejectsShortPassword(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $password = 'short';

        $this->profileService
            ->expects($this->once())
            ->method('canPasswordBeEmpty')
            ->with($password)
            ->willReturn(true);

        $this->profileService
            ->expects($this->once())
            ->method('isPasswordLongEnough')
            ->with($password)
            ->willReturn(false);

        $this->profileService
            ->expects($this->never())
            ->method('savePassword');

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/change_password',
            [
                'admin_change_password' => [
                    'newPassword' => $password,
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/admin/users/'.$user->getId().'/change_password'
        );
    }

    /**
     * Valid password is saved.
     */
    public function testChangePasswordSavesValidPassword(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $password = 'new-valid-password';

        $this->profileService
            ->expects($this->once())
            ->method('canPasswordBeEmpty')
            ->with($password)
            ->willReturn(true);

        $this->profileService
            ->expects($this->once())
            ->method('isPasswordLongEnough')
            ->with($password)
            ->willReturn(true);

        $this->profileService
            ->expects($this->once())
            ->method('savePassword')
            ->with(
                $this->identicalTo($user),
                $password
            );

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/change_password',
            [
                'admin_change_password' => [
                    'newPassword' => $password,
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/admin/users/'.$user->getId().'/edit'
        );
    }

    /**
     * User can be blocked.
     */
    public function testBlockUser(): void
    {
        $client = static::createClient();

        $admin = $this->getAdmin();
        $user = $this->getUser();

        $this->adminService
            ->expects($this->once())
            ->method('toggleBlock')
            ->with(
                $this->identicalTo($user),
                $this->identicalTo($admin)
            );

        $client->loginUser($admin);

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/block'
        );

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Requests page displays pending events.
     */
    public function testRequestsPage(): void
    {
        $client = static::createClient();

        $pagination = $this->createMock(
            PaginationInterface::class
        );

        $this->eventService
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(
                1,
                null,
                null,
                null,
                'pending'
            )
            ->willReturn($pagination);

        $client->loginUser($this->getAdmin());

        $client->request('GET', '/admin/requests');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Requests page passes page number.
     */
    public function testRequestsPageWithPage(): void
    {
        $client = static::createClient();

        $pagination = $this->createMock(
            PaginationInterface::class
        );

        $this->eventService
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(
                4,
                null,
                null,
                null,
                'pending'
            )
            ->willReturn($pagination);

        $client->loginUser($this->getAdmin());

        $client->request(
            'GET',
            '/admin/requests?page=4'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Pending event can be approved.
     */
    public function testApproveEvent(): void
    {
        $client = static::createClient();

        $event = $this->getPendingEvent();

        $this->adminService
            ->expects($this->once())
            ->method('approveEvent')
            ->with(
                $this->identicalTo($event)
            );

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/requests/'.$event->getId().'/approve'
        );

        $this->assertResponseRedirects('/admin/requests');
    }

    /**
     * Pending event can be rejected.
     */
    public function testRejectEvent(): void
    {
        $client = static::createClient();

        $event = $this->getPendingEvent();

        $this->adminService
            ->expects($this->once())
            ->method('rejectEvent')
            ->with(
                $this->identicalTo($event)
            );

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/requests/'.$event->getId().'/reject'
        );

        $this->assertResponseRedirects('/admin/requests');
    }

    /**
     * Administrator role can be added.
     */
    public function testToggleAdminRoleSuccess(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $this->adminService
            ->expects($this->once())
            ->method('toggleAdminRole')
            ->with(
                $this->identicalTo($user)
            );

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/toggle-admin'
        );

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Logic exception while toggling administrator role is handled.
     */
    public function testToggleAdminRoleHandlesLogicException(): void
    {
        $client = static::createClient();

        $user = $this->getUser();

        $this->adminService
            ->expects($this->once())
            ->method('toggleAdminRole')
            ->with(
                $this->identicalTo($user)
            )
            ->willThrowException(
                new \LogicException(
                    'Cannot remove administrator role.'
                )
            );

        $client->loginUser($this->getAdmin());

        $client->request(
            'POST',
            '/admin/users/'.$user->getId().'/toggle-admin'
        );

        $this->assertResponseRedirects('/admin/users');
    }
}

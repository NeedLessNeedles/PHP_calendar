<?php

/**
 * Tests for AdminController.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use App\Service\AdminServiceInterface;
use App\Service\ProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class AdminControllerTest.
 */
class AdminControllerTest extends WebTestCase
{
    /**
     * Browser client.
     */
    private KernelBrowser $client;

    /**
     * Entity manager interface.
     */
    private EntityManagerInterface $manager;

    /**
     * Create client and get entity manager.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->manager = static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * Index requires authentication.
     */
    public function testIndexRequiresLogin(): void
    {
        $this->client->request('GET', '/admin');

        $this->assertResponseRedirects();
    }

    /**
     * Administrator can view index.
     */
    public function testIndexAsAdmin(): void
    {
        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    /**
     * Users list uses profile service.
     */
    public function testUsersList(): void
    {
        $profileService = $this->mockProfileService();
        $pagination = $this->createPagination();

        $profileService
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(1)
            ->willReturn($pagination);

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Users list passes requested page.
     */
    public function testUsersListWithPage(): void
    {
        $profileService = $this->mockProfileService();
        $pagination = $this->createPagination(3);

        $profileService
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(3)
            ->willReturn($pagination);

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
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
        $admin = $this->persistAdmin();
        $user = $this->persistUser();

        $this->client->loginUser($admin);

        $this->client->request(
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
        $admin = $this->persistAdmin();
        $user = $this->persistUser();

        $this->client->loginUser($admin);

        $this->client->request(
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
        $profileService = $this->mockProfileService();
        $user = $this->persistUser();

        $profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with('')
            ->willReturn(false);

        $profileService
            ->expects($this->never())
            ->method('isEmailUnique');

        $profileService
            ->expects($this->never())
            ->method('saveEmail');

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
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
        $profileService = $this->mockProfileService();
        $user = $this->persistUser();

        $profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with('duplicate@example.com')
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('isEmailUnique')
            ->with(
                $this->identicalTo($user),
                'duplicate@example.com'
            )
            ->willReturn(false);

        $profileService
            ->expects($this->never())
            ->method('saveEmail');

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
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
     * Change password page can be displayed.
     */
    public function testChangePasswordGet(): void
    {
        $admin = $this->persistAdmin();
        $user = $this->persistUser();

        $this->client->loginUser($admin);

        $this->client->request(
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
        $profileService = $this->mockProfileService();

        $user = $this->persistUser();

        $profileService
            ->expects($this->once())
            ->method('canPasswordBeEmpty')
            ->with('')
            ->willReturn(false);

        $profileService
            ->expects($this->never())
            ->method('isPasswordLongEnough');

        $profileService
            ->expects($this->never())
            ->method('savePassword');

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
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
        $profileService = $this->mockProfileService();
        $user = $this->persistUser();

        $password = 'short';

        $profileService
            ->expects($this->once())
            ->method('canPasswordBeEmpty')
            ->with($password)
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('isPasswordLongEnough')
            ->with($password)
            ->willReturn(false);

        $profileService
            ->expects($this->never())
            ->method('savePassword');

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
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
     * User can be blocked.
     */
    public function testBlockUser(): void
    {
        $adminService = $this->mockAdminService();

        $admin = $this->persistAdmin();
        $user = $this->persistUser();

        $this->client->loginUser($admin);

        $adminService
            ->expects($this->once())
            ->method('toggleBlock')
            ->with(
                $this->identicalTo($user),
                $this->identicalTo($admin)
            );

        $this->client->request(
            'POST',
            '/admin/users/'.$user->getId().'/block'
        );

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Pending event can be approved.
     */
    public function testApproveEvent(): void
    {
        $adminService = $this->mockAdminService();

        $admin = $this->persistAdmin();
        $event = $this->persistPendingEvent();

        $adminService
            ->expects($this->once())
            ->method('approveEvent')
            ->with(
                $this->identicalTo($event)
            );

        $this->client->loginUser($admin);

        $this->client->request(
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
        $adminService = $this->mockAdminService();

        $admin = $this->persistAdmin();
        $event = $this->persistPendingEvent();

        $adminService
            ->expects($this->once())
            ->method('rejectEvent')
            ->with(
                $this->identicalTo($event)
            );

        $this->client->loginUser($admin);

        $this->client->request(
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
        $adminService = $this->mockAdminService();
        $user = $this->persistUser();

        $adminService
            ->expects($this->once())
            ->method('toggleAdminRole')
            ->with(
                $this->identicalTo($user)
            );

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
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
        $adminService = $this->mockAdminService();
        $user = $this->persistUser();

        $adminService
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

        $admin = $this->persistAdmin();
        $this->client->loginUser($admin);

        $this->client->request(
            'POST',
            '/admin/users/'.$user->getId().'/toggle-admin'
        );

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Helper.
     *
     * @param User|null $owner Owner
     *
     * @return Event event
     */
    private function persistPendingEvent(?User $owner = null): Event
    {
        $event = new Event();

        $event->setTitle(
            'Pending event '.uniqid('', true)
        );
        $event->setDescription('Test description');
        $event->setLocation('Krakow');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+1 day 2 hours'));
        $event->setStatus('pending');
        $event->setOwner($owner);
        $event->setCategory($this->persistCategory());

        $this->manager->persist($event);
        $this->manager->flush();

        return $event;
    }

    /**
     * Helper.
     *
     * @return ProfileServiceInterface&MockObject Profile service interface and Mock object
     */
    private function mockProfileService(): ProfileServiceInterface&MockObject
    {
        $service = $this->createMock(ProfileServiceInterface::class);

        static::getContainer()->set(
            ProfileServiceInterface::class,
            $service
        );

        return $service;
    }

    /**
     * Helper.
     *
     * @return AdminServiceInterface&MockObject Admin service interface and Mock object
     */
    private function mockAdminService(): AdminServiceInterface&MockObject
    {
        $service = $this->createMock(AdminServiceInterface::class);

        static::getContainer()->set(
            AdminServiceInterface::class,
            $service
        );

        return $service;
    }

    /**
     * Helper.
     *
     * @param int $page Page number
     *
     * @return SlidingPagination Sliding pagination
     */
    private function createPagination(int $page = 1): SlidingPagination
    {
        $pagination = new SlidingPagination([
            'page' => $page,
            'sortField' => 'user.email',
            'sortDirection' => 'desc',
        ]);

        $pagination->setCurrentPageNumber($page);
        $pagination->setItemNumberPerPage(10);
        $pagination->setTotalItemCount(0);
        $pagination->setUsedRoute('app_admin_users');
        $pagination->setSortableTemplate('@KnpPaginator/Pagination/sortable_link.html.twig');
        $pagination->setTemplate('@KnpPaginator/Pagination/sliding.html.twig');

        return $pagination;
    }

    /**
     * Helper for creating the example category.
     *
     * @return Category Category
     */
    private function createCategory(): Category
    {
        $category = new Category();

        $category->setTitle('Admin test category '.uniqid('', true));

        return $category;
    }

    /**
     * Helper for persisting the example category.
     *
     * @return Category Category
     */
    private function persistCategory(): Category
    {
        $category = $this->createCategory();

        $this->manager->persist($category);
        $this->manager->flush();

        return $category;
    }

    /**
     * Helper for creating users.
     *
     * @param array $roles Roles
     *
     * @return User admin user
     */
    private function createUser(array $roles = ['ROLE_USER']): User
    {
        $user = new User();

        $prefix = in_array('ROLE_ADMIN', $roles, true)
            ? 'admin-test-'
            : 'user-test-';

        $user->setEmail(
            $prefix.uniqid('', true).'@test.com'
        );
        $user->setPassword('password');
        $user->setRoles($roles);

        return $user;
    }

    /**
     * Helper for persisting the example admin.
     *
     * @return User user
     */
    private function persistAdmin(): User
    {
        $admin = $this->createUser(
            ['ROLE_USER', 'ROLE_ADMIN']
        );

        $this->manager->persist($admin);
        $this->manager->flush();

        return $admin;
    }

    /**
     * Helper for persisting the example user.
     *
     * @return User user
     */
    private function persistUser(): User
    {
        $user = $this->createUser();

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}

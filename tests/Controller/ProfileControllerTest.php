<?php

/**
 * Tests for ProfileController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\ProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class ProfileControllerTest.
 */
class ProfileControllerTest extends WebTestCase
{
    /**
     * Logged user can view profile.
     */
    public function testIndex(): void
    {
        $client = static::createClient();

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
    }

    /**
     * Change password page can be displayed.
     */
    public function testChangePasswordGet(): void
    {
        $client = static::createClient();

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'GET',
            '/profile/change_password'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test for changing password.
     */
    public function testChangePasswordRequiresLogin(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/profile/change_password'
        );

        $this->assertResponseRedirects('/login');
    }

    /**
     * Test for changing email.
     */
    public function testChangeEmailRequiresLogin(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/profile/change_email'
        );

        $this->assertResponseRedirects('/login');
    }

    /**
     * Test for changing password.
     */
    public function testChangePasswordInvalidForm(): void
    {
        $client = static::createClient();
        $profileService = $this->mockProfileService($client);
        $password = 'valid-password';

        $profileService
            ->expects($this->once())
            ->method('canPasswordBeEmpty')
            ->with($password)
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('isPasswordLongEnough')
            ->with($password)
            ->willReturn(true);

        $profileService
            ->expects($this->never())
            ->method('savePassword');

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_password',
            [
                'change_password' => [
                    'newPassword' => $password,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test for changing email.
     */
    public function testChangeEmailInvalidForm(): void
    {
        $client = static::createClient();
        $profileService = $this->mockProfileService($client);
        $email = 'not-an-email';

        $profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with($email)
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('isEmailUnique')
            ->willReturn(true);

        $profileService
            ->expects($this->never())
            ->method('saveEmail');

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_email',
            [
                'change_email' => [
                    'email' => $email,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Empty password is rejected.
     */
    public function testChangePasswordRejectsEmptyPassword(): void
    {
        $client = static::createClient();
        $profileService = $this->mockProfileService($client);

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

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_password',
            [
                'change_password' => [
                    'currentPassword' => 'anything',
                    'newPassword' => '',
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/profile/change_password'
        );
    }

    /**
     * Too short password is rejected.
     */
    public function testChangePasswordRejectsShortPassword(): void
    {
        $client = static::createClient();
        $profileService = $this->mockProfileService($client);
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

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_password',
            [
                'change_password' => [
                    'currentPassword' => 'anything',
                    'newPassword' => $password,
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/profile/change_password'
        );
    }

    /**
     * Change email page can be displayed.
     */
    public function testChangeEmailGet(): void
    {
        $client = static::createClient();

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'GET',
            '/profile/change_email'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Empty email is rejected.
     */
    public function testChangeEmailRejectsEmptyEmail(): void
    {
        $client = static::createClient();
        $profileService = $this->mockProfileService($client);

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

        $user = $this->persistUser($client);
        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_email',
            [
                'change_email' => [
                    'email' => '',
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/profile/change_email'
        );
    }

    /**
     * Duplicate email is rejected.
     */
    public function testChangeEmailRejectsDuplicateEmail(): void
    {
        $client = static::createClient();
        $profileService = $this->mockProfileService($client);
        $user = $this->persistUser($client);
        $email = 'duplicate@example.com';

        $profileService
            ->expects($this->once())
            ->method('canBeEmpty')
            ->with($email)
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('isEmailUnique')
            ->with(
                $this->identicalTo($user),
                $email
            )
            ->willReturn(false);

        $profileService
            ->expects($this->never())
            ->method('saveEmail');

        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_email',
            [
                'change_email' => [
                    'email' => $email,
                ],
            ]
        );

        $this->assertResponseRedirects(
            '/profile/change_email'
        );
    }

    /**
     * Helper.
     *
     * @param KernelBrowser $client client
     *
     * @return EntityManagerInterface Entity manager interface
     */
    private function getEntityManager(KernelBrowser $client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Helper.
     *
     * @param <string> $client Client
     *
     * @return ProfileServiceInterface&MockObject Profile service interface and Mock object
     */
    private function mockProfileService($client): ProfileServiceInterface&MockObject
    {
        $service = $this->createMock(ProfileServiceInterface::class);

        $client->getContainer()->set(
            ProfileServiceInterface::class,
            $service
        );

        return $service;
    }

    /**
     * Helper for persisting the example user.
     *
     * @param KernelBrowser $client Client
     *
     * @return User user
     */
    private function persistUser(KernelBrowser $client): User
    {
        $user = $this->createUser();

        $this->getEntityManager($client)->persist($user);
        $this->getEntityManager($client)->flush();

        return $user;
    }

    /**
     * Helper for creating regular user.
     *
     * @return User admin user
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('profile-test-'.uniqid('', true).'@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        return $user;
    }
}

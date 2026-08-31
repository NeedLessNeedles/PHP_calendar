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

/**
 * Class ProfileControllerTest.
 */
class ProfileControllerTest extends WebTestCase
{
    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
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
     * Logged user can view profile.
     */
    public function testIndex(): void
    {
        $client = static::createClient();

        $user = $this->getUser($client);
        $client->loginUser($user);

        $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Change password page can be displayed.
     */
    public function testChangePasswordGet(): void
    {
        $client = static::createClient();

        $user = $this->getUser($client);
        $client->loginUser($user);

        $client->request(
            'GET',
            '/profile/change_password'
        );

        $this->assertResponseIsSuccessful();
    }

    public function testChangePasswordRequiresLogin(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/profile/change_password'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testChangeEmailRequiresLogin(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/profile/change_email'
        );

        $this->assertResponseStatusCodeSame(403);
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

        $user = $this->getUser($client);
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

        $user = $this->getUser($client);
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
     * Valid password is saved.
     */
    public function testChangePasswordSavesValidPassword(): void
    {
        $client = static::createClient();

        $profileService = $this->mockProfileService($client);

        $user = $this->getUser($client);
        $client->loginUser($user);

        $profileService
            ->method('canPasswordBeEmpty')
            ->willReturn(true);

        $profileService
            ->method('isPasswordLongEnough')
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('savePassword')
            ->with(
                $this->identicalTo($user),
                'new-valid-password'
            );

        $client->request(
            'POST',
            '/profile/change_password',
            [
                'change_password' => [
                    'currentPassword' => 'current-password',
                    'newPassword' => 'new-valid-password',
                ],
            ]
        );

        $this->assertResponseRedirects('/profile');
    }

    /**
     * Change email page can be displayed.
     */
    public function testChangeEmailGet(): void
    {
        $client = static::createClient();

        $user = $this->getUser($client);
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

        $user = $this->getUser($client);
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

        $user = $this->getUser($client);
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
     * Valid email is saved.
     */
    public function testChangeEmailSavesValidEmail(): void
    {
        $client = static::createClient();

        $profileService = $this->mockProfileService($client);

        $user = $this->getUser($client);

        $profileService
            ->method('canBeEmpty')
            ->willReturn(true);

        $profileService
            ->method('isEmailUnique')
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('saveEmail')
            ->with(
                $this->identicalTo($user),
                'new-profile-email@example.com'
            );

        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/change_email',
            [
                'change_email' => [
                    'email' => 'new-profile-email@example.com',
                ],
            ]
        );

        $this->assertResponseRedirects('/profile');
    }
}

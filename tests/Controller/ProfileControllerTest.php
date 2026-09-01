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

        $this->assertResponseStatusCodeSame(403);
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

        $this->assertResponseStatusCodeSame(403);
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

        $user = $this->getUser($client);
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

        $user = $this->getUser($client);
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
        $password = 'new-valid-password';

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
            ->expects($this->once())
            ->method('savePassword')
            ->with(
                $this->identicalTo($user),
                $password
            );

        $client->loginUser($user);

        $token = $this->getCsrfToken(
            $client,
            '/profile/change_password',
            'input[name="change_password[_token]"]'
        );

        $client->request(
            'POST',
            '/profile/change_password',
            [
                'change_password' => [
                    'currentPassword' => 'anything',
                    'newPassword' => $password,
                    '_token' => $token,
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
        $email = 'new-profile-email@example.com';

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
            ->willReturn(true);

        $profileService
            ->expects($this->once())
            ->method('saveEmail')
            ->with(
                $this->identicalTo($user),
                $email
            );

        $client->loginUser($user);

        $token = $this->getCsrfToken(
            $client,
            '/profile/change_email',
            'input[name="change_email[_token]"]'
        );

        $client->request(
            'POST',
            '/profile/change_email',
            [
                'change_email' => [
                    'email' => $email,
                    '_token' => $token,
                ],
            ]
        );

        $this->assertResponseRedirects('/profile');
    }

    /**
     * Helper.
     *
     * @param User $client client
     *
     * @return EntityManagerInterface Entity manager interface
     */
    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Helper.
     *
     * @param User $client client
     *
     * @return User user
     */
    private function getUser(User $client): User
    {
        $user = $this->getEntityManager($client)
            ->getRepository(User::class)
            ->findOneBy([
                'email' => 'user.first@gmail.com',
            ]);

        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    /**
     * Helper.
     *
     * @param string $client   Client
     * @param string $url      URL
     * @param string $selector Selector
     *
     * @return string Token
     */
    private function getCsrfToken(string $client, string $url, string $selector): string
    {
        $client->request('GET', $url);

        $token = $client->getCrawler()
            ->filter($selector)
            ->attr('value');

        $this->assertNotNull($token);

        return $token;
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
}

<?php

/**
 * Tests for EventVoter.
 */

namespace App\Tests\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use App\Security\Voter\EventVoter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class EventVoterTest.
 */
class EventVoterTest extends KernelTestCase
{
    private AuthorizationCheckerInterface $auth;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->auth = self::getContainer()->get(AuthorizationCheckerInterface::class);
    }

    public function testVoterServiceExists(): void
    {
        $voter = self::getContainer()->get(EventVoter::class);

        $this->assertInstanceOf(EventVoter::class, $voter);
    }

    public function testViewAllowedForAnonymous(): void
    {
        $event = new Event();

        $this->assertTrue($this->auth->isGranted(EventVoter::VIEW, $event));
    }

    public function testEditDeniedForAnonymous(): void
    {
        $event = new Event();

        $this->assertFalse($this->auth->isGranted(EventVoter::EDIT, $event));
    }

    public function testAdminCanEdit(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $event = new Event();
        $event->setOwner($user);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::getContainer()->get('security.token_storage')->setToken($token);
        $this->assertTrue($this->auth->isGranted(EventVoter::EDIT, $event));
    }

    public function testOwnerCanEdit(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $event = new Event();
        $event->setOwner($user);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::getContainer()->get('security.token_storage')->setToken($token);
        $this->assertTrue($this->auth->isGranted(EventVoter::EDIT, $event));
    }

    public function testOwnerCanDeleteOnlyIfApproved(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $event = new Event();
        $event->setOwner($user);
        $event->setStatus('approved');

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);

        $this->assertTrue($this->auth->isGranted(EventVoter::DELETE, $event));
    }

    public function testOwnerCannotDeleteIfNotApproved(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $event = new Event();
        $event->setOwner($user);
        $event->setStatus('pending');

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);

        $this->assertFalse($this->auth->isGranted(EventVoter::DELETE, $event));
    }
}

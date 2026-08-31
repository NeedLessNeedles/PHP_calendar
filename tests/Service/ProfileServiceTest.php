<?php

/**
 * Tests for ProfileService.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ProfileService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ProfileServiceTest.
 */
class ProfileServiceTest extends TestCase
{
    private UserRepository $userRepository;

    private PaginatorInterface $paginator;

    private UserPasswordHasherInterface $passwordHasher;

    private ProfileService $service;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createStub(
            UserRepository::class
        );

        $this->paginator = $this->createStub(
            PaginatorInterface::class
        );

        $this->passwordHasher = $this->createStub(
            UserPasswordHasherInterface::class
        );

        $this->service = new ProfileService(
            $this->userRepository,
            $this->paginator,
            $this->passwordHasher
        );
    }

    /**
     * Test for pagination.
     */
    public function testGetPaginatedList(): void
    {
        $queryBuilder = $this->createStub(QueryBuilder::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $userRepository = $this->createMock(
            UserRepository::class
        );

        $paginator = $this->createMock(
            PaginatorInterface::class
        );

        $userRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $paginator
            ->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                2,
                ProfileService::PAGINATOR_ITEMS_PER_PAGE,
                [
                    'sortFieldAllowList' => ['user.email'],
                    'defaultSortFieldName' => 'user.email',
                    'defaultSortDirection' => 'desc',
                ]
            )
            ->willReturn($pagination);

        $this->service = new ProfileService(
            $userRepository,
            $paginator,
            $this->passwordHasher
        );

        $result = $this->service->getPaginatedList(2);

        $this->assertSame($pagination, $result);
    }

    /**
     * Test for saving the password.
     */
    public function testSavePassword(): void
    {
        $userRepository = $this->createMock(
            UserRepository::class
        );

        $passwordHasher = $this->createMock(
            UserPasswordHasherInterface::class
        );

        $user = new User();

        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'new-password')
            ->willReturn('hashed-password');

        $userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->service = new ProfileService(
            $userRepository,
            $this->paginator,
            $passwordHasher
        );

        $this->service->savePassword($user, 'new-password');

        $this->assertSame(
            'hashed-password',
            $user->getPassword()
        );
    }

    /**
     * Test for saving the email.
     */
    public function testSaveEmail(): void
    {
        $userRepository = $this->createMock(
            UserRepository::class
        );

        $user = new User();

        $userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->service = new ProfileService(
            $userRepository,
            $this->paginator,
            $this->passwordHasher
        );

        $this->service->saveEmail($user, 'new@test.com');

        $this->assertSame(
            'new@test.com',
            $user->getEmail()
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForNull(): void
    {
        $this->assertFalse(
            $this->service->canBeEmpty(null)
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForBlankEmail(): void
    {
        $this->assertFalse(
            $this->service->canBeEmpty('   ')
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsTrueForValidEmail(): void
    {
        $this->assertTrue(
            $this->service->canBeEmpty('test@test.com')
        );
    }

    /**
     * Test for isEmailUnique() method.
     */
    public function testIsEmailUniqueReturnsFalseForNull(): void
    {
        $user = new User();

        $this->assertFalse(
            $this->service->isEmailUnique($user, null)
        );
    }

    /**
     * Test for isEmailUnique() method.
     */
    public function testIsEmailUniqueReturnsFalseForBlankEmail(): void
    {
        $user = new User();

        $this->assertFalse(
            $this->service->isEmailUnique($user, '   ')
        );
    }

    /**
     * Test for isEmailUnique() method.
     */
    public function testIsEmailUniqueReturnsTrueWhenUserDoesNotExist(): void
    {
        $userRepository = $this->createMock(
            UserRepository::class
        );

        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@test.com'])
            ->willReturn(null);

        $this->service = new ProfileService(
            $userRepository,
            $this->paginator,
            $this->passwordHasher
        );

        $user = new User();

        $this->assertTrue(
            $this->service->isEmailUnique(
                $user,
                'test@test.com'
            )
        );
    }

    /**
     * Test for isEmailUnique() method.
     */
    public function testIsEmailUniqueReturnsTrueForSameUser(): void
    {
        $userRepository = $this->createMock(
            UserRepository::class
        );

        $user = new User();

        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, 1);

        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@test.com'])
            ->willReturn($user);

        $this->service = new ProfileService(
            $userRepository,
            $this->paginator,
            $this->passwordHasher
        );

        $this->assertTrue(
            $this->service->isEmailUnique(
                $user,
                'test@test.com'
            )
        );
    }

    /**
     * Test for isEmailUnique() method.
     */
    public function testIsEmailUniqueReturnsFalseForAnotherUser(): void
    {
        $userRepository = $this->createMock(
            UserRepository::class
        );

        $user = new User();
        $existingUser = new User();

        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, 1);

        $reflection = new \ReflectionClass($existingUser);
        $property = $reflection->getProperty('id');
        $property->setValue($existingUser, 2);

        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@test.com'])
            ->willReturn($existingUser);

        $this->service = new ProfileService(
            $userRepository,
            $this->paginator,
            $this->passwordHasher
        );

        $this->assertFalse(
            $this->service->isEmailUnique(
                $user,
                'test@test.com'
            )
        );
    }

    /**
     * Test for canPasswordBeEmpty() method.
     */
    public function testCanPasswordBeEmptyReturnsFalseForNull(): void
    {
        $this->assertFalse(
            $this->service->canPasswordBeEmpty(null)
        );
    }

    /**
     * Test for canPasswordBeEmpty() method.
     */
    public function testCanPasswordBeEmptyReturnsFalseForBlankPassword(): void
    {
        $this->assertFalse(
            $this->service->canPasswordBeEmpty('   ')
        );
    }

    /**
     * Test for canPasswordBeEmpty() method.
     */
    public function testCanPasswordBeEmptyReturnsTrueForValidPassword(): void
    {
        $this->assertTrue(
            $this->service->canPasswordBeEmpty('password')
        );
    }

    /**
     * Test for isPasswordLongEnough() method.
     */
    public function testIsPasswordLongEnoughReturnsFalseForNull(): void
    {
        $this->assertFalse(
            $this->service->isPasswordLongEnough(null)
        );
    }

    /**
     * Test for isPasswordLongEnough() method.
     */
    public function testIsPasswordLongEnoughReturnsFalseForShortPassword(): void
    {
        $this->assertFalse(
            $this->service->isPasswordLongEnough('1234567')
        );
    }

    /**
     * Test for isPasswordLongEnough() method.
     */
    public function testIsPasswordLongEnoughReturnsTrueForEightCharacters(): void
    {
        $this->assertTrue(
            $this->service->isPasswordLongEnough('12345678')
        );
    }
}

<?php
/**
 * User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Class UserFixtures.
 */
class UserFixtures extends AbstractBaseFixtures implements FixtureGroupInterface
{
    /**
     * Load data.
     */
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public static function getGroups(): array
    {
        return ['main'];
    }

    public function loadData(): void
    {
        $admins = [
            'admin.first@gmail.com',
            'admin.second@gmail.com',
        ];
        $defaultUsers = [
            'user.first@gmail.com',
            'user.second@gmail.com',
        ];

        foreach($admins as $admin) {
            $user = new User();
            $user->setEmail($admin);
            $user->setRoles(['ROLE_ADMIN']);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'admin123'
            );
            $user->setPassword($hashedPassword);
            $this->manager->persist($user);
        }

        foreach($defaultUsers as $defaultUser) {
            $user = new User();
            $user->setEmail($defaultUser);
            $user->setRoles(['ROLE_USER']);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'user123'
            );
            $user->setPassword($hashedPassword);
            $this->manager->persist($user);
        }

        $this->manager->flush();
    }
}

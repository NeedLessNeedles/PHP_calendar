<?php

/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class CategoryFixtures extends AbstractBaseFixtures implements FixtureGroupInterface
{
    /**
     * Load data.
     */
    public static function getGroups(): array
    {
        return ['main'];
    }
    public function loadData(): void
    {
        $titles = [
            'Music',
            'Sport',
            'Education',
            'Technology',
            'Art',
        ];

        foreach ($titles as $title) {
            $category = new Category();
            $category->setTitle($title);

            $this->manager->persist($category);
        }

        $this->manager->flush();
    }
}

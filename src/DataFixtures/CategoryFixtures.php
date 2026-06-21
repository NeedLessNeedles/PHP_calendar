<?php

/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Class CategoryFixtures.
 */
class CategoryFixtures extends AbstractBaseFixtures implements FixtureGroupInterface
{
    /**
     * Get destined fixture groups.
     *
     * @return array Group name list
     */
    public static function getGroups(): array
    {
        return ['main'];
    }

    /**
     * Load data.
     */
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

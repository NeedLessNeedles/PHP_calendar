<?php

/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class TagFixtures extends AbstractBaseFixtures implements FixtureGroupInterface
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
        $tags = [
            'free',
            'online',
            'sold out',
            'for_kids',
            'for_adults',
            'family_friendly',
            'private',
            'open',
            'ticketed',
        ];

        foreach ($tags as $title) {
            $tag = new Tag();
            $tag->setTitle($title);

            $this->manager->persist($tag);
        }

        $this->manager->flush();
    }
}

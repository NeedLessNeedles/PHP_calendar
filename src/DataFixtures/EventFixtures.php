<?php
/**
 * Event fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Tag;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class EventFixtures.
 */
class EventFixtures extends AbstractBaseFixtures implements DependentFixtureInterface, FixtureGroupInterface
{
    /**
     * Load data.
     */
    public static function getGroups(): array
    {
        return ['main'];
    }
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }
    public function loadData(): void
    {
        $categories = $this->manager
            ->getRepository(Category::class)
            ->findAll();
        $tags = $this->manager
            ->getRepository(Tag::class)
            ->findAll();

        for ($i = 0; $i < 20; ++$i) {
            $event = new Event();
            $event->setTitle($this->faker->sentence);
            $event->setDescription($this->faker->sentence);
            $event->setLocation($this->faker->city());
            $event->setStartDate(
                ($this->faker->dateTimeBetween('-10 days', '+1 days'))
            );
            $event->setStatus('approved');
            $event->setCategory(
                $this->faker->randomElement($categories)
            );
            $randomTags = $this->faker->randomElements($tags, rand(1, 2));
            foreach ($randomTags as $tag) {
                $event->addTag($tag);
            }

            $this->manager->persist($event);
        }

        $this->manager->flush();
    }
}

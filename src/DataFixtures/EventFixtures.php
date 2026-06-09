<?php
/**
 * Event fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Category;
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
        ];
    }
    public function loadData(): void
    {
        $categories = $this->manager
            ->getRepository(Category::class)
            ->findAll();

        for ($i = 0; $i < 10; ++$i) {
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

            $this->manager->persist($event);
        }

        $this->manager->flush();
    }
}

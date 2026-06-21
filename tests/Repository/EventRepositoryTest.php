<?php

/**
 * Tests for EventRepository.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Tag;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EventRepositoryTest.
 */
class EventRepositoryTest extends KernelTestCase
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;

    /**
     * Constructor test.
     */
    public function testRepositoryCanBeCreated(): void
    {
        self::bootKernel();
        $repo = self::getContainer()->get(EventRepository::class);

        $this->assertInstanceOf(EventRepository::class, $repo);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->eventRepository = $container->get(EventRepository::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testQueryAllWithoutFilters(): void
    {
        $qb = $this->eventRepository->queryAll();

        $this->assertStringContainsString('SELECT', $qb->getQuery()->getDQL());
    }

    public function testQueryAllWithTitleFilter(): void
    {
        $qb = $this->eventRepository->queryAll(null, 'test');

        $this->assertStringContainsString('LOWER(event.title)', $qb->getDQL());
    }
}

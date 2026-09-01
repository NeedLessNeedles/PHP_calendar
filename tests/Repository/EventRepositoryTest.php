<?php

/**
 * Tests for EventRepository.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\Event;
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

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->eventRepository = $container->get(EventRepository::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    /**
     * Test for query builder without filters.
     */
    public function testQueryAllWithoutFilters(): void
    {
        $qb = $this->eventRepository->queryAll();
        $dql = $qb->getDQL();

        $this->assertStringContainsString(
            'SELECT',
            $dql
        );

        $this->assertStringContainsString(
            'LEFT JOIN event.category category',
            $dql
        );

        $this->assertStringContainsString(
            'LEFT JOIN event.tags tag',
            $dql
        );
    }

    /**
     * Test for query builder with filter for Title.
     */
    public function testQueryAllWithTitleFilter(): void
    {
        $qb = $this->eventRepository->queryAll(null, 'test');

        $this->assertStringContainsString(
            'LOWER(event.title) LIKE LOWER(:title)',
            $qb->getDQL()
        );

        $this->assertSame(
            '%test%',
            $qb->getParameter('title')->getValue()
        );
    }

    /**
     * Tests if filter allows empty Title.
     */
    public function testQueryAllWithEmptyTitleDoesNotAddFilter(): void
    {
        $qb = $this->eventRepository->queryAll(
            title: ''
        );

        $this->assertStringNotContainsString(
            'LOWER(event.title) LIKE LOWER(:title)',
            $qb->getDQL()
        );

        $this->assertNull(
            $qb->getParameter('title')
        );
    }

    /**
     * Test for query builder with filter for Category.
     */
    public function testQueryAllWithCategoryFilter(): void
    {
        $qb = $this->eventRepository->queryAll(
            categoryId: 1
        );

        $this->assertStringContainsString(
            'category.id = :categoryId',
            $qb->getDQL()
        );

        $this->assertSame(
            1,
            $qb->getParameter('categoryId')->getValue()
        );
    }

    /**
     * Test for query builder with filter for Tag.
     */
    public function testQueryAllWithTagFilter(): void
    {
        $qb = $this->eventRepository->queryAll(
            tagId: 5
        );

        $this->assertStringContainsString(
            ':tagId MEMBER OF event.tags',
            $qb->getDQL()
        );

        $this->assertSame(
            5,
            $qb->getParameter('tagId')->getValue()
        );
    }

    /**
     * Test saving event.
     */
    public function testSave(): void
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([]);

        $this->assertInstanceOf(Category::class, $category);

        $event = new Event();
        $event->setTitle('Repository test event');
        $event->setStartDate(new \DateTime('2026-01-01'));
        $event->setCategory($category);
        $event->setStatus('approved');

        $this->eventRepository->save($event);
        $this->assertNotNull($event->getId());

        $savedEvent = $this->eventRepository->find(
            $event->getId()
        );

        $this->assertInstanceOf(Event::class, $savedEvent);
        $this->assertSame(
            'Repository test event',
            $savedEvent->getTitle()
        );
    }

    /**
     * Test deleting event.
     */
    public function testDelete(): void
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([]);

        $this->assertInstanceOf(Category::class, $category);

        $event = new Event();
        $event->setTitle('Event to delete');
        $event->setStartDate(new \DateTime('2026-01-02'));
        $event->setCategory($category);
        $event->setStatus('approved');

        $this->eventRepository->save($event);

        $eventId = $event->getId();
        $this->assertNotNull($eventId);
        $this->eventRepository->delete($event);
        $deletedEvent = $this->eventRepository->find($eventId);
        $this->assertNull($deletedEvent);
    }

    /**
     * Test counting events by category.
     */
    public function testCountByCategory(): void
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([]);

        $this->assertInstanceOf(Category::class, $category);

        $initialCount = $this->eventRepository->countByCategory(
            $category
        );

        $event1 = new Event();
        $event1->setTitle('Count test event 1');
        $event1->setStartDate(new \DateTime('2026-02-01'));
        $event1->setCategory($category);
        $event1->setStatus('approved');

        $event2 = new Event();
        $event2->setTitle('Count test event 2');
        $event2->setStartDate(new \DateTime('2026-02-02'));
        $event2->setCategory($category);
        $event2->setStatus('approved');

        $this->eventRepository->save($event1);
        $this->eventRepository->save($event2);

        $count = $this->eventRepository->countByCategory(
            $category
        );

        $this->assertSame(
            $initialCount + 2,
            $count
        );
    }

    /**
     * Test status filtering.
     */
    public function testQueryAllWithStatusFilter(): void
    {
        $qb = $this->eventRepository->queryAll(
            status: 'approved'
        );

        $this->assertStringContainsString(
            'event.status = :status',
            $qb->getDQL()
        );

        $this->assertSame(
            'approved',
            $qb->getParameter('status')->getValue()
        );
    }

    /**
     * Test finding approved events for ICS export.
     */
    public function testFindEventsForIcsExport(): void
    {
        $events = $this->eventRepository->findEventsForIcsExport();

        $this->assertIsArray($events);

        foreach ($events as $event) {
            $this->assertInstanceOf(Event::class, $event);

            $this->assertSame(
                'approved',
                $event->getStatus()
            );
        }
    }

    /**
     * Test events for ICS export are sorted by start date.
     */
    public function testEventsForIcsExportSortedByStartDate(): void
    {
        $events = $this->eventRepository->findEventsForIcsExport();

        $previousDate = null;

        foreach ($events as $event) {
            $currentDate = $event->getStartDate();

            $this->assertNotNull($currentDate);

            if (null !== $previousDate) {
                $this->assertGreaterThanOrEqual(
                    $previousDate,
                    $currentDate
                );
            }

            $previousDate = $currentDate;
        }
    }
}

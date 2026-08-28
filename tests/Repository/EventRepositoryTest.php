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
     * Test finding events for ICS export.
     */
    public function testFindEventsForIcsExport(): void
    {
        $events = $this->eventRepository->findEventsForIcsExport();

        dump($events);
        dump(count($events));

        $this->assertIsArray($events);
    }
}

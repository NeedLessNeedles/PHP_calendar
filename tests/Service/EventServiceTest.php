<?php

/**
 * Tests for EventService.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Repository\TagRepository;
use App\Service\EventService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class EventServiceTest.
 */
class EventServiceTest extends TestCase
{
    private EventRepository $eventRepository;

    private CategoryRepository $categoryRepository;

    private TagRepository $tagRepository;

    private PaginatorInterface $paginator;

    private EventService $service;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->eventRepository = $this->createStub(
            EventRepository::class
        );

        $this->categoryRepository = $this->createStub(
            CategoryRepository::class
        );

        $this->tagRepository = $this->createStub(
            TagRepository::class
        );

        $this->paginator = $this->createStub(
            PaginatorInterface::class
        );

        $this->service = new EventService(
            $this->eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );
    }

    /**
     * Test for pagination.
     */
    public function testGetPaginatedList(): void
    {
        $queryBuilder = $this->createStub(QueryBuilder::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $paginator = $this->createMock(
            PaginatorInterface::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('queryAll')
            ->with(2, 'Music', 3, 'approved')
            ->willReturn($queryBuilder);

        $paginator
            ->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                2,
                5,
                [
                    'sortFieldAllowList' => [
                        'event.startDate',
                        'event.title',
                    ],
                    'defaultSortFieldName' => 'event.startDate',
                    'defaultSortDirection' => 'desc',
                ]
            )
            ->willReturn($pagination);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $paginator
        );

        $result = $this->service->getPaginatedList(
            2,
            2,
            'Music',
            3,
            'approved'
        );

        $this->assertSame($pagination, $result);
    }

    /**
     * Test for getCategories() method.
     */
    public function testGetCategories(): void
    {
        $categories = [
            new Category(),
            new Category(),
        ];

        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $categoryRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($categories);

        $this->service = new EventService(
            $this->eventRepository,
            $categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->assertSame(
            $categories,
            $this->service->getCategories()
        );
    }

    /**
     * Test for getTags() method.
     */
    public function testGetTags(): void
    {
        $tags = [
            new Tag(),
            new Tag(),
        ];

        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $tagRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($tags);

        $this->service = new EventService(
            $this->eventRepository,
            $this->categoryRepository,
            $tagRepository,
            $this->paginator
        );

        $this->assertSame(
            $tags,
            $this->service->getTags()
        );
    }

    /**
     * Test for saving event as an admin.
     */
    public function testSaveWithAdminUser(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Event::class));

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $event = new Event();

        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $this->service->save($event, $user);

        $this->assertSame('approved', $event->getStatus());
        $this->assertSame($user, $event->getOwner());
    }

    /**
     * Test for saving event as a user.
     */
    public function testSaveWithRegularUser(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Event::class));

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $event = new Event();

        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $this->service->save($event, $user);

        $this->assertSame('approved', $event->getStatus());
        $this->assertSame($user, $event->getOwner());
    }

    /**
     * Test for saving request for event as a non-logged user.
     */
    public function testSaveWithoutUser(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Event::class));

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $event = new Event();

        $this->service->save($event, null);

        $this->assertSame('pending', $event->getStatus());
        $this->assertNull($event->getOwner());
    }

    /**
     * Test for delete() method.
     */
    public function testDelete(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $event = new Event();

        $eventRepository
            ->expects($this->once())
            ->method('delete')
            ->with($event);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->service->delete($event);
    }

    /**
     * Test for creating event as an admin.
     */
    public function testCreateWithAdminUser(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $category = new Category();
        $event = new Event();
        $event->setCategory($category);

        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->service->create($event, $user);

        $this->assertSame('approved', $event->getStatus());
        $this->assertSame($user, $event->getOwner());
    }

    /**
     * Test for creating event as a user.
     */
    public function testCreateWithRegularUser(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $category = new Category();
        $event = new Event();
        $event->setCategory($category);

        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->service->create($event, $user);

        $this->assertSame('approved', $event->getStatus());
        $this->assertSame($user, $event->getOwner());
    }

    /**
     * Test for creating request for an event as a non-logged user.
     */
    public function testCreateWithoutUser(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $category = new Category();
        $event = new Event();
        $event->setCategory($category);

        $eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->service->create($event, null);

        $this->assertSame('pending', $event->getStatus());
        $this->assertNull($event->getOwner());
    }

    /**
     * Test if event can be created without category.
     */
    public function testCreateThrowsWhenCategoryIsMissing(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->never())
            ->method('save');

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $event = new Event();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Category is required');

        $this->service->create($event, null);
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForNullTitle(): void
    {
        $event = new Event();

        $this->assertFalse(
            $this->service->canBeEmpty($event)
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForBlankTitle(): void
    {
        $event = new Event();
        $event->setTitle('   ');
        $event->setStartDate(new \DateTime());

        $this->assertFalse(
            $this->service->canBeEmpty($event)
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForMissingStartDate(): void
    {
        $event = new Event();
        $event->setTitle('Music event');

        $this->assertFalse(
            $this->service->canBeEmpty($event)
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsTrueForValidEvent(): void
    {
        $event = new Event();
        $event->setTitle('Music event');
        $event->setStartDate(new \DateTime());

        $this->assertTrue(
            $this->service->canBeEmpty($event)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsFalseForNullTitle(): void
    {
        $event = new Event();

        $this->assertFalse(
            $this->service->isTitleUnique($event)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsFalseForBlankTitle(): void
    {
        $event = new Event();
        $event->setTitle('   ');

        $this->assertFalse(
            $this->service->isTitleUnique($event)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsTrueWhenEventDoesNotExist(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn(null);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $event = new Event();
        $event->setTitle('Music');

        $this->assertTrue(
            $this->service->isTitleUnique($event)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsTrueForSameEvent(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $event = new Event();
        $event->setTitle('Music');

        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('id');
        $property->setValue($event, 1);

        $eventRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn($event);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->assertTrue(
            $this->service->isTitleUnique($event)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsFalseForAnotherEvent(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $event = new Event();
        $event->setTitle('Music');

        $existingEvent = new Event();

        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('id');
        $property->setValue($event, 1);

        $reflection = new \ReflectionClass($existingEvent);
        $property = $reflection->getProperty('id');
        $property->setValue($existingEvent, 2);

        $eventRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn($existingEvent);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $this->assertFalse(
            $this->service->isTitleUnique($event)
        );
    }

    /**
     * Test for exportToIcs() method.
     */
    public function testExportToIcsReturnsEmptyCalendar(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $eventRepository
            ->expects($this->once())
            ->method('findEventsForIcsExport')
            ->willReturn([]);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $result = $this->service->exportToIcs();

        $expected = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Local Events Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'END:VCALENDAR',
            '',
        ]);

        $this->assertSame($expected, $result);
    }

    /**
     * Test for exportToIcs() method.
     */
    public function testExportToIcsIncludesEventData(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $event = new Event();
        $event->setTitle('Concert; Rock, Roll');
        $event->setDescription("Line one\nLine two");
        $event->setLocation('Main Hall; Krakow');

        $startDate = new \DateTime(
            '2026-08-31 18:30:00',
            new \DateTimeZone('Europe/Warsaw')
        );

        $endDate = new \DateTime(
            '2026-08-31 20:30:00',
            new \DateTimeZone('Europe/Warsaw')
        );

        $event->setStartDate($startDate);
        $event->setEndDate($endDate);

        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('id');
        $property->setValue($event, 10);

        $eventRepository
            ->expects($this->once())
            ->method('findEventsForIcsExport')
            ->willReturn([$event]);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $result = $this->service->exportToIcs();

        $this->assertStringContainsString('BEGIN:VEVENT', $result);
        $this->assertStringContainsString('UID:event-10@local-events-calendar', $result);
        $this->assertStringContainsString('DTSTART:20260831T163000Z', $result);
        $this->assertStringContainsString('DTEND:20260831T183000Z', $result);
        $this->assertStringContainsString('SUMMARY:Concert\; Rock\, Roll', $result);
        $this->assertStringContainsString('DESCRIPTION:Line one\nLine two', $result);
        $this->assertStringContainsString('LOCATION:Main Hall\; Krakow', $result);
        $this->assertStringContainsString('END:VEVENT', $result);
    }

    /**
     * Test for exportToIcs() method.
     */
    public function testExportToIcsHandlesMissingOptionalFields(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $event = new Event();
        $event->setTitle('Simple event');

        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('id');
        $property->setValue($event, 11);

        $eventRepository
            ->expects($this->once())
            ->method('findEventsForIcsExport')
            ->willReturn([$event]);

        $this->service = new EventService(
            $eventRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->paginator
        );

        $result = $this->service->exportToIcs();

        $this->assertStringContainsString('UID:event-11@local-events-calendar', $result);
        $this->assertStringContainsString('DTSTART:', $result);
        $this->assertStringNotContainsString('DTEND:', $result);
        $this->assertStringContainsString('SUMMARY:Simple event', $result);
        $this->assertStringNotContainsString('DESCRIPTION:', $result);
        $this->assertStringNotContainsString('LOCATION:', $result);
    }
}

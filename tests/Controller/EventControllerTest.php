<?php

/**
 * Tests for EventController.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use App\Service\EventServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class EventControllerTest.
 */
class EventControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $manager;

    /**
     * Index page can be displayed.
     */
    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/event');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Events list');

        self::assertGreaterThanOrEqual(
            1,
            $crawler->filter('body')->count()
        );
    }

    /**
     * Index supports filters.
     */
    public function testIndexWithFilters(): void
    {
        $service = $this->mockEventService();

        $pagination = $this->createMock(PaginationInterface::class);

        $service
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(2, 3, 'concert', 4, null)
            ->willReturn($pagination);

        $service
            ->expects($this->once())
            ->method('getCategories')
            ->willReturn([]);

        $service
            ->expects($this->once())
            ->method('getTags')
            ->willReturn([]);

        $this->client->request('GET', '/event', [
            'page' => '2',
            'categoryId' => '3',
            'title' => 'concert',
            'tagId' => '4',
        ]);

        self::assertResponseIsSuccessful();
    }

    /**
     * New event page can be displayed.
     */
    public function testNewGet(): void
    {
        $service = $this->mockEventService();

        $service
            ->method('getCategories')
            ->willReturn([]);

        $service
            ->method('getTags')
            ->willReturn([]);

        $this->client->request('GET', '/event/new');

        self::assertResponseIsSuccessful();
    }

    /**
     * Empty title is rejected.
     */
    public function testNewRejectsEmptyTitle(): void
    {
        $service = $this->mockEventService();

        $service
            ->expects($this->never())
            ->method('save');

        $service
            ->expects($this->never())
            ->method('create');

        $this->loginUser();

        $this->client->request('POST', '/event/new', [
            'event' => [
                'title' => '',
                'description' => 'Description',
                'location' => 'Krakow',
                'startDate' => (new \DateTime('+1 day'))
                    ->format('Y-m-d\TH:i'),
                'endDate' => '',
                'category' => $this->getCategoryId(),
                'tags' => [],
            ],
        ]);

        self::assertResponseRedirects('/event/new');
    }

    /**
     * Duplicate title is rejected.
     */
    public function testNewRejectsDuplicateTitle(): void
    {
        $service = $this->mockEventService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('save');

        $service
            ->expects($this->never())
            ->method('create');

        $this->loginUser();

        $this->client->request('POST', '/event/new', [
            'event' => [
                'title' => 'Duplicate event',
                'description' => 'Description',
                'location' => 'Krakow',
                'startDate' => (new \DateTime('+1 day'))
                    ->format('Y-m-d\TH:i'),
                'endDate' => '',
                'category' => $this->getCategoryId(),
                'tags' => [],
            ],
        ]);

        self::assertResponseRedirects('/event/new');
    }

    /**
     * Valid event is saved.
     */
    public function testNewSavesValidEvent(): void
    {
        $service = $this->mockEventService();

        $user = $this->loginUser();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(true);

        $service
            ->method('save');

        $service
            ->method('create');

        $crawler = $this->client->request('GET', '/event/new');

        self::assertResponseIsSuccessful();

        $categoryOptions = $crawler->filter(
            'select[name="event[category]"] option'
        );

        self::assertGreaterThan(
            0,
            $categoryOptions->count(),
            'There must be at least one category available.'
        );

        $categoryValue = $categoryOptions
            ->first()
            ->attr('value');

        self::assertNotNull($categoryValue);

        $form = $crawler->filter('form')->form();

        $form['event[title]'] = 'Testing valid event';
        $form['event[description]'] = 'Testing description';
        $form['event[location]'] = 'Warsaw';
        $form['event[startDate]'] = '2026-09-10T18:00';
        $form['event[category]'] = $categoryValue;

        $this->client->submit($form);

        self::assertResponseRedirects('/event');

        unset($user);
    }

    /**
     * Show event page.
     */
    public function testShow(): void
    {
        $event = $this->persistEvent();

        $this->client->request(
            'GET',
            '/event/'.$event->getId()
        );

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Event');
    }

    /**
     * Edit event page can be displayed.
     */
    public function testEditGet(): void
    {
        $event = $this->persistEvent();

        $this->loginUser();

        $this->client->request(
            'GET',
            '/event/'.$event->getId().'/edit'
        );

        self::assertResponseIsSuccessful();
    }

    /**
     * Empty title is rejected during edit.
     */
    public function testEditRejectsEmptyTitle(): void
    {
        $event = $this->persistEvent();

        $service = $this->mockEventService();

        $service
            ->expects($this->never())
            ->method('save');

        $service
            ->expects($this->never())
            ->method('create');

        $this->loginUser();

        $crawler = $this->client->request(
            'GET',
            '/event/'.$event->getId().'/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        $form['event[title]'] = '';
        $form['event[description]'] = 'Updated description';
        $form['event[location]'] = 'Warsaw';

        $this->client->submit($form);

        self::assertResponseRedirects(
            '/event/'.$event->getId().'/edit'
        );
    }

    /**
     * Duplicate title is rejected during edit.
     */
    public function testEditRejectsDuplicateTitle(): void
    {
        $event = $this->persistEvent();

        $service = $this->mockEventService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('save');

        $service
            ->expects($this->never())
            ->method('create');

        $this->loginUser();

        $crawler = $this->client->request(
            'GET',
            '/event/'.$event->getId().'/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        $form['event[title]'] = 'Duplicate title';

        $this->client->submit($form);

        self::assertResponseRedirects(
            '/event/'.$event->getId().'/edit'
        );
    }

    /**
     * Valid event is saved during edit.
     */
    public function testEditSavesValidEvent(): void
    {
        $event = $this->persistEvent();

        $service = $this->mockEventService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(true);

        $service
            ->method('save');

        $service
            ->method('create');

        $user = $this->loginUser();

        $crawler = $this->client->request(
            'GET',
            '/event/'.$event->getId().'/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        $form['event[title]'] = 'Updated event';
        $form['event[description]'] = 'Updated description';
        $form['event[location]'] = 'Updated location';

        $this->client->submit($form);

        self::assertResponseRedirects('/event');

        unset($user);
    }

    /**
     * Delete page can be displayed.
     */
    public function testDeleteGet(): void
    {
        $event = $this->persistEvent();

        $this->loginUser();

        $this->client->request(
            'GET',
            '/event/'.$event->getId().'/delete'
        );

        self::assertResponseIsSuccessful();
    }

    /**
     * Event can be deleted.
     */
    public function testDelete(): void
    {
        $event = $this->persistEvent();

        $service = $this->mockEventService();

        $service
            ->expects($this->once())
            ->method('delete')
            ->with($this->isInstanceOf(Event::class));

        $this->loginUser();

        $crawler = $this->client->request(
            'GET',
            '/event/'.$event->getId().'/delete'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        $this->client->submit($form);

        self::assertResponseRedirects('/event');
    }

    /**
     * Export events to ICS.
     */
    public function testExportIcs(): void
    {
        $service = $this->mockEventService();

        $ics = "BEGIN:VCALENDAR\r\n"
            ."VERSION:2.0\r\n"
            ."BEGIN:VEVENT\r\n"
            ."SUMMARY:Test event\r\n"
            ."END:VEVENT\r\n"
            ."END:VCALENDAR\r\n";

        $service
            ->expects($this->once())
            ->method('exportToIcs')
            ->willReturn($ics);

        $this->client->request('GET', '/event/export');

        self::assertResponseStatusCodeSame(200);

        self::assertResponseHeaderSame(
            'Content-Type',
            'text/calendar; charset=UTF-8'
        );

        self::assertResponseHeaderSame(
            'Content-Disposition',
            'attachment; filename="events.ics"'
        );

        self::assertStringContainsString(
            'BEGIN:VCALENDAR',
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * Create client and get entity manager.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->manager = static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * Get event service mock.
     *
     * @return EventServiceInterface Event service interface
     */
    private function mockEventService(): EventServiceInterface
    {
        $service = $this->createMock(EventServiceInterface::class);

        static::getContainer()->set(
            EventServiceInterface::class,
            $service
        );

        return $service;
    }

    /**
     * Get existing user.
     *
     * @param string $email Email
     *
     * @return User User
     */
    private function getUser(string $email = 'user.first@gmail.com'): User
    {
        $user = $this->manager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    /**
     * Create event fixture.
     *
     * The event is not persisted.
     *
     * @param string $title Event title
     *
     * @return Event Event
     */
    private function createEvent(string $title = 'Test event'): Event
    {
        $event = new Event();

        $event->setTitle($title);
        $event->setDescription('Test description');
        $event->setLocation('Krakow');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+1 day 2 hours'));
        $event->setStatus('approved');

        $category = $this->manager
            ->getRepository(Category::class)
            ->findOneBy([]);

        if ($category instanceof Category) {
            $event->setCategory($category);
        }

        return $event;
    }

    /**
     * Persist event fixture.
     *
     * @param string $title Event title
     *
     * @return Event Persisted event
     */
    private function persistEvent(string $title = 'Test event'): Event
    {
        $event = $this->createEvent($title);

        self::assertInstanceOf(
            Category::class,
            $event->getCategory(),
            'At least one category fixture is required.'
        );

        $this->manager->persist($event);
        $this->manager->flush();

        return $event;
    }

    /**
     * Log user in.
     *
     * @return User Logged user
     */
    private function loginUser(): User
    {
        $user = $this->getUser();

        $this->client->loginUser($user);

        return $user;
    }

    /**
     * Get event from fixtures.
     *
     * @param string $title Event title
     *
     * @return Event|null Event
     */
    private function getEvent(string $title = 'Test event'): ?Event
    {
        return $this->manager
            ->getRepository(Event::class)
            ->findOneBy(['title' => $title]);
    }

    /**
     * Get category ID from fixtures.
     *
     * @return int Category ID
     */
    private function getCategoryId(): int
    {
        $category = $this->manager
            ->getRepository(Category::class)
            ->findOneBy([]);

        self::assertInstanceOf(Category::class, $category);

        return $category->getId();
    }
}

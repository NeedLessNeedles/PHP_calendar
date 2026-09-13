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
        $pagination = $this->createStub(PaginationInterface::class);

        $service
            ->expects($this->once())
            ->method('getPaginatedList')
            ->with(null, 2, 3, 'concert', 4, null)
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
        $service = $this->createStub(EventServiceInterface::class);

        $service->method('getCategories')->willReturn([]);
        $service->method('getTags')->willReturn([]);

        static::getContainer()->set(
            EventServiceInterface::class,
            $service
        );

        $this->client->request('GET', '/event/new');

        self::assertResponseIsSuccessful();
    }

    /**
     * Empty title is rejected.
     */
    public function testNewRejectsEmptyTitle(): void
    {
        $service = $this->mockEventService();
        $category = $this->persistCategory();

        $service
            ->expects($this->never())
            ->method('save');

        $this->loginUser();

        $this->client->request('POST', '/event/new', [
            'event' => [
                'title' => '',
                'description' => 'Description',
                'location' => 'Krakow',
                'startDate' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i'),
                'endDate' => '',
                'category' => $category->getId(),
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
        $category = $this->persistCategory();

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

        $this->loginUser();

        $this->client->request('POST', '/event/new', [
            'event' => [
                'title' => 'Duplicate event',
                'description' => 'Description',
                'location' => 'Krakow',
                'startDate' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i'),
                'endDate' => '',
                'category' => $category->getId(),
                'tags' => [],
            ],
        ]);

        self::assertResponseRedirects('/event/new');
    }

    /**
     * Edit event page can be displayed.
     */
    public function testEditGet(): void
    {
        $user = $this->loginUser();
        $event = $this->persistEvent(owner: $user);

        $this->client->request(
            'GET',
            '/event/'.$event->getId().'/edit'
        );

        self::assertResponseIsSuccessful();
    }

    /**
     * User cannot edit another user's event.
     */
    public function testEditRejectsAnotherUsersEvent(): void
    {
        $owner = $this->persistUser();

        $event = $this->persistEvent(
            'Other user event '.uniqid(),
            $owner
        );

        $otherUser = $this->persistUser();
        $this->client->loginUser($otherUser);

        $this->client->request(
            'GET',
            '/event/'.$event->getId().'/edit'
        );

        self::assertResponseStatusCodeSame(403);
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
     * Helper for creating the example event.
     *
     * The event is not persisted.
     *
     * @param string    $title Event title
     * @param User|null $owner Owner
     *
     * @return Event Event
     */
    private function createEvent(string $title = 'Test event', ?User $owner = null): Event
    {
        $event = new Event();

        $event->setTitle($title);
        $event->setDescription('Test description');
        $event->setLocation('Krakow');
        $event->setStartDate(new \DateTime('+1 day'));
        $event->setEndDate(new \DateTime('+1 day 2 hours'));
        $event->setStatus('approved');
        $event->setOwner($owner);
        $event->setCategory($this->persistCategory());

        return $event;
    }

    /**
     * Helper for persisting the example event.
     *
     * @param string    $title Event title
     * @param User|null $owner Owner
     *
     * @return Event Persisted event
     */
    private function persistEvent(string $title = 'Test event', ?User $owner = null): Event
    {
        $event = $this->createEvent($title, $owner);

        self::assertInstanceOf(
            Category::class,
            $event->getCategory(),
            'Event category cannot be empty.'
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
        $user = $this->persistUser();

        $this->client->loginUser($user);

        return $user;
    }

    /**
     * Helper for creating the example category.
     *
     * @return Category Category
     */
    private function createCategory(): Category
    {
        $category = new Category();

        $category->setTitle('Event test category '.uniqid('', true));

        return $category;
    }

    /**
     * Helper for persisting the example category.
     *
     * @return Category Category
     */
    private function persistCategory(): Category
    {
        $category = $this->createCategory();

        $this->manager->persist($category);
        $this->manager->flush();

        return $category;
    }

    /**
     * Helper for persisting the example user.
     *
     * @return User user
     */
    private function persistUser(): User
    {
        $user = $this->createUser();

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    /**
     * Helper for creating regular user.
     *
     * @return User admin user
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('event-test-'.uniqid('', true).'@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        return $user;
    }
}

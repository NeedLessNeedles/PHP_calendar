<?php

/**
 * Tests for EventController.
 */

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class EventControllerTest.
 */
class EventControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Event> */
    private EntityRepository $eventRepository;
    private string $path = '/event/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->eventRepository = $this->manager->getRepository(Event::class);

        foreach ($this->eventRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    private function getUser($client, string $email): User
    {
        return $client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    private function getEvent($client): ?Event
    {
        return $client->getContainer()
            ->get('doctrine')
            ->getRepository(Event::class)
            ->findOneBy([]);
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'event[title]' => 'Testing',
            'event[description]' => 'Testing',
            'event[eventDate]' => 'Testing',
            'event[location]' => 'Testing',
        ]);

        self::assertResponseRedirects('/event');

        self::assertSame(1, $this->eventRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Event();
        $fixture->setTitle('My Title');
        $fixture->setDescription('My Title');
        $fixture->setEventDate('My Title');
        $fixture->setLocation('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Event();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        $fixture->setEventDate('Value');
        $fixture->setLocation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'event[title]' => 'Something New',
            'event[description]' => 'Something New',
            'event[eventDate]' => 'Something New',
            'event[location]' => 'Something New',
        ]);

        self::assertResponseRedirects('/event');

        $fixture = $this->eventRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getEventDate());
        self::assertSame('Something New', $fixture[0]->getLocation());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Event();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        $fixture->setEventDate('Value');
        $fixture->setLocation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/event');
        self::assertSame(0, $this->eventRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testNewEventAsUser(): void
    {
        $client = static::createClient();

        $user = $this->getUser($client, 'user.first@gmail.com');
        $client->loginUser($user);

        $client->request('POST', '/event/new', [
            'event' => [
                'title' => 'Test event',
                'description' => 'desc',
                'location' => 'Warsaw',
            ],
        ]);

        $this->assertResponseRedirects('/event/calendar');
    }

    public function testEditEventDeniedWithoutPermission(): void
    {
        $client = static::createClient();

        $event = $this->getEvent($client);
        $this->assertNotNull($event);

        $client->request('POST', '/event/' . $event->getId() . '/edit', [
            'event' => [
                'title' => 'Changed title',
            ],
        ]);

        $this->assertResponseRedirects();
    }

    public function testEditEventAsOwner(): void
    {
        $client = static::createClient();

        $user = $this->getUser($client, 'user.first@gmail.com');
        $event = $this->getEvent($client);

        $client->loginUser($user);

        $client->request('POST', '/event/' . $event->getId() . '/edit', [
            'event' => [
                'title' => 'Updated event',
            ],
        ]);

        $this->assertResponseRedirects('/event');
    }

    public function testDeleteEventAsOwner(): void
    {
        $client = static::createClient();

        $user = $this->getUser($client, 'user.first@gmail.com');
        $event = $this->getEvent($client);

        $client->loginUser($user);

        $client->request('POST', '/event/' . $event->getId(), [
            '_token' => 'delete' . $event->getId(),
        ]);

        $this->assertResponseRedirects('/event');
    }

    public function testJsonEvents(): void
    {
        $client = static::createClient();

        $client->request('GET', '/event/json');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testSingleEventJson(): void
    {
        $client = static::createClient();

        $event = $this->getEvent($client);

        $client->request('GET', '/event/' . $event->getId() . '/json');

        $this->assertResponseIsSuccessful();
    }

    public function testCalendarPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/event/calendar');

        $this->assertResponseIsSuccessful();
    }

}

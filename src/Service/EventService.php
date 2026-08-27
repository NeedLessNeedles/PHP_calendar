<?php

/**
 * Event service.
 */

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EventService.
 */
class EventService implements EventServiceInterface
{
    public const PAGINATOR_ITEMS_PER_PAGE = 2;

    /**
     * Constructor.
     *
     * @param EventRepository        $eventRepository Event repository
     * @param PaginatorInterface     $paginator       Paginator
     * @param EntityManagerInterface $entityManager   Entity manager
     */
    public function __construct(private readonly EventRepository $eventRepository, private readonly PaginatorInterface $paginator, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int         $page       Page number
     * @param int|null    $categoryId Category ID
     * @param string|null $title      Title
     * @param int|null    $tagId      Tag ID
     * @param string|null $status     Status
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?int $categoryId = null, ?string $title = null, ?int $tagId = null, ?string $status = null): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->eventRepository->queryAll($categoryId, $title, $tagId, $status),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['event.startDate', 'event.title'],
                'defaultSortFieldName' => 'event.startDate',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Create event.
     *
     * @param Event     $event Event
     * @param User|null $user  User
     */
    public function create(Event $event, ?User $user): void
    {
        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $event->setStatus('approved');
        } elseif ($user) {
            $event->setStatus('approved');
        } else {
            $event->setStatus('pending');
        }

        if (!$event->getCategory()) {
            throw new \LogicException('Category is required');
        }
        $event->setOwner($user);

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    /**
     * Export approved current and upcoming events to ICS format.
     *
     * @return string ICS content
     */
    public function exportToIcs(): string
    {
        $events = $this->eventRepository->findEventsForIcsExport();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Local Events Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';

            $lines[] = 'UID:event-'.$event->getId().'@local-events-calendar';
            $lines[] = 'DTSTAMP:'.gmdate('Ymd\THis\Z');

            $lines[] = 'DTSTART:'.$this->formatIcsDate($event->getStartDate());

            if ($event->getEndDate()) {
                $lines[] = 'DTEND:'.$this->formatIcsDate($event->getEndDate());
            }

            $lines[] = 'SUMMARY:'.$this->escapeIcsText($event->getTitle());

            if ($event->getDescription()) {
                $lines[] = 'DESCRIPTION:'.$this->escapeIcsText($event->getDescription());
            }

            if ($event->getLocation()) {
                $lines[] = 'LOCATION:'.$this->escapeIcsText($event->getLocation());
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * Format date for ICS.
     *
     * @param \DateTime|null $date Date
     *
     * @return string Formatted date
     */
    private function formatIcsDate(?\DateTime $date): string
    {
        if (!$date) {
            return '';
        }

        $date = clone $date;
        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format('Ymd\THis\Z');
    }

    /**
     * Escape text for ICS format.
     *
     * @param string $text Text
     *
     * @return string Escaped text
     */
    private function escapeIcsText(string $text): string
    {
        return str_replace(
            ["\\", ";", ",", "\r\n", "\r", "\n"],
            ["\\\\", "\;", "\,", "\\n", "\\n", "\\n"],
            $text
        );
    }
}

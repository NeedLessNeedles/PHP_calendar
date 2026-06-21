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
    public const PAGINATOR_ITEMS_PER_PAGE = 5;

    /**
     * Constructor.
     *
     * @param EventRepository    $eventRepository Event repository
     * @param PaginatorInterface $paginator       Paginator
     */
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly PaginatorInterface $paginator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getPaginatedList(int $page, ?int $categoryId = null, ?string $title = null, ?int $tagId = null): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->eventRepository->queryAll($categoryId, $title, $tagId),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['event.startDate', 'event.title'],
                'defaultSortFieldName' => 'event.startDate',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

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
}

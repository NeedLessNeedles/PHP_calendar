<?php

/**
 * Event repository.
 */

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EventRepository.
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Query all records.
     *
     * @param int|null    $categoryId Category ID
     * @param string|null $title      Title
     * @param int|null    $tagId      Tag ID
     * @param string|null $status     Status
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(?int $categoryId = null, ?string $title = null, ?int $tagId = null, ?string $status = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.category', 'category')
            ->addSelect('category')
            ->leftJoin('event.tags', 'tag')
            ->addSelect('tag');

        if (null !== $categoryId) {
            $queryBuilder
                ->andWhere('category.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if (null !== $tagId) {
            $queryBuilder
                ->andWhere(':tagId MEMBER OF event.tags')
                ->setParameter('tagId', $tagId);
        }

        if (null !== $title && '' !== $title) {
            $queryBuilder
                ->andWhere('LOWER(event.title) LIKE LOWER(:title)')
                ->setParameter('title', '%'.$title.'%');
        }

        if (null !== $status) {
            $queryBuilder
                ->andWhere('event.status = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }

    /**
     * Save entity.
     *
     * @param Event $event Event entity
     */
    public function save(Event $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Event $event Event entity
     */
    public function delete(Event $event): void
    {
        $this->getEntityManager()->remove($event);
        $this->getEntityManager()->flush();
    }

    /**
     * Find approved events for ICS export.
     *
     * @return Event[] Events
     */
    public function findEventsForIcsExport(): array
    {
        return $this->createQueryBuilder('event')
            ->andWhere('event.status = :status')
            ->setParameter('status', 'approved')
            ->orderBy('event.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

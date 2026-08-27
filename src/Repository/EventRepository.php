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
    public const PAGINATOR_ITEMS_PER_PAGE = 5;

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
}

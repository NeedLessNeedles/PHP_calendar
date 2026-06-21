<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(?int $categoryId = null, ?string $title = null, ?int $tagId = null): QueryBuilder
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

        return $queryBuilder;
    }
}

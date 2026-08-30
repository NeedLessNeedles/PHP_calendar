<?php

/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\EventRepository;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 3;

    /**
     * Constructor.
     *
     * @param CategoryRepository     $categoryRepository Category repository
     * @param PaginatorInterface     $paginator          Paginator
     * @param EventRepository        $eventRepository    Event repository
     */
    public function __construct(private readonly CategoryRepository $categoryRepository, private readonly PaginatorInterface $paginator, private readonly EventRepository $eventRepository)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->categoryRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['category.createdAt', 'category.updatedAt'],
                'defaultSortFieldName' => 'category.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        $category->setUpdatedAt(new \DateTimeImmutable());
        if (null === $category->getId()) {
            $category->setCreatedAt(new \DateTimeImmutable());
        }
        $this->categoryRepository->save($category);
    }

    /**
     * Delete entity.
     *
     * @param Category $category Category
     */
    public function delete(Category $category): void
    {
        $usedByEvents = $this->eventRepository->count([
            'category' => $category,
        ]);

        if ($usedByEvents > 0) {
            throw new \DomainException('Cannot delete category used by events.');
        }

        $this->categoryRepository->delete($category);
    }
}

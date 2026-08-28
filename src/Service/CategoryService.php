<?php

/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\EventRepository;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
    public const PAGINATOR_ITEMS_PER_PAGE = 5;

    /**
     * Constructor.
     *
     * @param CategoryRepository     $categoryRepository Category repository
     * @param EntityManagerInterface $entityManager      Entity manager
     * @param PaginatorInterface     $paginator          Paginator
     * @param EventRepository        $eventRepository    Event repository
     */
    public function __construct(private readonly CategoryRepository $categoryRepository, private readonly EntityManagerInterface $entityManager, private readonly PaginatorInterface $paginator, private readonly EventRepository $eventRepository)
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
     * Edit category.
     *
     * @param Category $category Category
     * @param string   $title    Title
     */
    public function edit(Category $category, string $title): void
    {
        $category->setTitle($title);
        $category->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    /**
     * Delete category.
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

        $this->entityManager->remove($category);
        $this->entityManager->flush();
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
}

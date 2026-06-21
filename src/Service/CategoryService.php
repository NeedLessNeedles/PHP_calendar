<?php

/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager Entity manager
     * @param EventRepository $eventRepository Event repository
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly EventRepository $eventRepository)
    {
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
}

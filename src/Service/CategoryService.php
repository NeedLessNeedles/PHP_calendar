<?php
/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    public function edit(Category $category, string $title): void
    {
        $category->setTitle($title);
        $category->setUpdatedAt(new \DateTimeImmutable());
    }

    public function delete(Category $category): void
    {
        $usedByEvents = $this->eventRepository->count([
            'category' => $category
        ]);

        if ($usedByEvents > 0) {
            throw new \DomainException('Cannot delete category used by events.');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}

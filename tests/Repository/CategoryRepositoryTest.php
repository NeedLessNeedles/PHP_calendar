<?php

/**
 * Tests for CategoryRepository.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryRepositoryTest.
 */
class CategoryRepositoryTest extends KernelTestCase
{
    private CategoryRepository $categoryRepository;

    /**
     * Constructor test.
     */
    public function testRepositoryCanBeCreated(): void
    {
        self::bootKernel();
        $repo = self::getContainer()->get(CategoryRepository::class);

        $this->assertInstanceOf(CategoryRepository::class, $repo);
    }

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->categoryRepository = self::getContainer()->get(CategoryRepository::class);
    }

    /**
     * Test Repository setup.
     */
    public function testRepositoryFromSetUp(): void
    {
        $this->assertInstanceOf(CategoryRepository::class, $this->categoryRepository);
    }

    /**
     * Test query builder.
     */
    public function testQueryAll(): void
    {
        $qb = $this->categoryRepository->queryAll();

        $this->assertStringContainsString(
            'SELECT',
            $qb->getDQL()
        );

        $this->assertStringContainsString(
            'FROM App\Entity\Category',
            $qb->getDQL()
        );
    }

    /**
     * Test saving category.
     */
    public function testSave(): void
    {
        $title = 'Repository test category '.uniqid();
        $category = new Category();
        $category->setTitle($title);
        $this->categoryRepository->save($category);
        $this->assertNotNull($category->getId());
        $savedCategory = $this->categoryRepository->find($category->getId());

        $this->assertInstanceOf(Category::class, $savedCategory);
        $this->assertSame($title, $savedCategory->getTitle());
    }

    /**
     * Test deleting category.
     */
    public function testDelete(): void
    {
        $category = new Category();
        $category->setTitle(
            'Repository category to delete'
        );

        $this->categoryRepository->save($category);
        $categoryId = $category->getId();
        $this->assertNotNull($categoryId);
        $this->categoryRepository->delete($category);

        $deletedCategory = $this->categoryRepository->find(
            $categoryId
        );

        $this->assertNull($deletedCategory);
    }
}

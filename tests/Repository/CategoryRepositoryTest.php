<?php

/**
 * Tests for CategoryRepository.
 */

namespace App\Tests\Repository;

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

    protected function setUp(): void
    {
        self::bootKernel();

        $this->categoryRepository = self::getContainer()->get(CategoryRepository::class);
    }

    public function testRepositoryFromSetUp(): void
    {
        $this->assertInstanceOf(CategoryRepository::class, $this->categoryRepository);
    }
}

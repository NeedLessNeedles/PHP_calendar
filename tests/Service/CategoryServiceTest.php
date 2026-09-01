<?php

/**
 * Tests for CategoryService.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Service\CategoryService;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends TestCase
{
    private CategoryRepository $categoryRepository;

    private PaginatorInterface $paginator;

    private EventRepository $eventRepository;

    private CategoryService $service;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->categoryRepository = $this->createStub(
            CategoryRepository::class
        );

        $this->paginator = $this->createStub(
            PaginatorInterface::class
        );

        $this->eventRepository = $this->createStub(
            EventRepository::class
        );

        $this->service = new CategoryService(
            $this->categoryRepository,
            $this->paginator,
            $this->eventRepository
        );
    }

    /**
     * Test for pagination.
     */
    public function testGetPaginatedList(): void
    {
        $queryBuilder = $this->createStub(QueryBuilder::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $paginator = $this->createMock(
            PaginatorInterface::class
        );

        $categoryRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $paginator
            ->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                2,
                CategoryService::PAGINATOR_ITEMS_PER_PAGE,
                [
                    'sortFieldAllowList' => [
                        'category.createdAt',
                        'category.updatedAt',
                    ],
                    'defaultSortFieldName' => 'category.createdAt',
                    'defaultSortDirection' => 'desc',
                ]
            )
            ->willReturn($pagination);

        $this->service = new CategoryService(
            $categoryRepository,
            $paginator,
            $this->eventRepository
        );

        $result = $this->service->getPaginatedList(2);

        $this->assertSame($pagination, $result);
    }

    /**
     * Test for save() method.
     */
    public function testSave(): void
    {
        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $categoryRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Category::class));

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $this->eventRepository
        );

        $category = new Category();

        $this->service->save($category);

        $this->assertNotNull($category->getCreatedAt());
        $this->assertNotNull($category->getUpdatedAt());
    }

    /**
     * Test if category with duplicated title can be created by save() method.
     */
    public function testSaveExistingCategory(): void
    {
        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $categoryRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Category::class));

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $this->eventRepository
        );

        $category = new Category();

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setValue($category, 1);

        $createdAt = new \DateTimeImmutable('2026-01-01 12:00:00');
        $category->setCreatedAt($createdAt);

        $this->service->save($category);

        $this->assertSame($createdAt, $category->getCreatedAt());
        $this->assertNotNull($category->getUpdatedAt());
    }

    /**
     * Test if category can be deleted.
     */
    public function testDeleteSuccess(): void
    {
        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $category = new Category();

        $eventRepository
            ->expects($this->once())
            ->method('count')
            ->with(['category' => $category])
            ->willReturn(0);

        $categoryRepository
            ->expects($this->once())
            ->method('delete')
            ->with($category);

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $eventRepository
        );

        $this->service->delete($category);
    }

    /**
     * Test if exception is thrown while deleting the category in use.
     */
    public function testDeleteThrowsWhenUsed(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $category = new Category();

        $eventRepository
            ->expects($this->once())
            ->method('count')
            ->with(['category' => $category])
            ->willReturn(3);

        $categoryRepository
            ->expects($this->never())
            ->method('delete');

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $eventRepository
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'Cannot delete category used by events.'
        );

        $this->service->delete($category);
    }

    /**
     * Test if empty title field returns false  for null value.
     */
    public function testCanBeEmptyReturnsFalseForNullTitle(): void
    {
        $category = new Category();

        $this->assertFalse(
            $this->service->canBeEmpty($category)
        );
    }

    /**
     * Test if empty title field returns false  for blank value.
     */
    public function testCanBeEmptyReturnsFalseForBlankTitle(): void
    {
        $category = new Category();
        $category->setTitle('   ');

        $this->assertFalse(
            $this->service->canBeEmpty($category)
        );
    }

    /**
     * Test if canBeEmpty() method returns true if the title is provided correctly.
     */
    public function testCanBeEmptyReturnsTrueForValidTitle(): void
    {
        $category = new Category();
        $category->setTitle('Music');

        $this->assertTrue(
            $this->service->canBeEmpty($category)
        );
    }

    /**
     * Test if isTitleUnique() method returns false for blank value.
     */
    public function testIsTitleUniqueReturnsFalseForBlankTitle(): void
    {
        $category = new Category();
        $category->setTitle('   ');

        $this->assertFalse(
            $this->service->isTitleUnique($category)
        );
    }

    /**
     * Test if isTitleUnique() method returns true if category we want to create does not exist yet.
     */
    public function testIsTitleUniqueReturnsTrueWhenCategoryDoesNotExist(): void
    {
        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $categoryRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn(null);

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $this->eventRepository
        );

        $category = new Category();
        $category->setTitle('Music');

        $this->assertTrue(
            $this->service->isTitleUnique($category)
        );
    }

    /**
     * Test if isTitleUnique() method returns true if category is in fact unique.
     */
    public function testIsTitleUniqueReturnsTrueForSameCategory(): void
    {
        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $category = new Category();
        $category->setTitle('Music');

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setValue($category, 1);

        $categoryRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn($category);

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $this->eventRepository
        );

        $this->assertTrue(
            $this->service->isTitleUnique($category)
        );
    }

    /**
     * Test if isTitleUnique() method returns false if category is duplicated.
     */
    public function testIsTitleUniqueReturnsFalseForAnotherCategory(): void
    {
        $categoryRepository = $this->createMock(
            CategoryRepository::class
        );

        $category = new Category();
        $category->setTitle('Music');

        $existingCategory = new Category();

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setValue($category, 1);

        $reflection = new \ReflectionClass($existingCategory);
        $property = $reflection->getProperty('id');
        $property->setValue($existingCategory, 2);

        $categoryRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn($existingCategory);

        $this->service = new CategoryService(
            $categoryRepository,
            $this->paginator,
            $this->eventRepository
        );

        $this->assertFalse(
            $this->service->isTitleUnique($category)
        );
    }

    /**
     * Test for canBeDeleted() method.
     */
    public function testCanBeDeletedReturnsTrueWhenCategoryIsUnused(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $category = new Category();

        $eventRepository
            ->expects($this->once())
            ->method('countByCategory')
            ->with($category)
            ->willReturn(0);

        $this->service = new CategoryService(
            $this->categoryRepository,
            $this->paginator,
            $eventRepository
        );

        $this->assertTrue(
            $this->service->canBeDeleted($category)
        );
    }

    /**
     * Test for canBeDeleted() method.
     */
    public function testCanBeDeletedReturnsFalseWhenCategoryIsUsed(): void
    {
        $eventRepository = $this->createMock(
            EventRepository::class
        );

        $category = new Category();

        $eventRepository
            ->expects($this->once())
            ->method('countByCategory')
            ->with($category)
            ->willReturn(2);

        $this->service = new CategoryService(
            $this->categoryRepository,
            $this->paginator,
            $eventRepository
        );

        $this->assertFalse(
            $this->service->canBeDeleted($category)
        );
    }
}

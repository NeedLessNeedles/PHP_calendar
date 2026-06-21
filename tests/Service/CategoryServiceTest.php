<?php

/**
 * Tests for CategoryService.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\EventRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends TestCase
{
    public function testEdit(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EventRepository::class);
        $service = new CategoryService($entityManager, $repo);
        $category = new Category();
        $oldUpdatedAt = $category->getUpdatedAt();
        $service->edit($category, 'New title');

        $this->assertSame('New title', $category->getTitle());
        $this->assertNotSame($oldUpdatedAt, $category->getUpdatedAt());
    }

    public function testDeleteSuccess(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EventRepository::class);
        $repo->method('count')->willReturn(0);
        $entityManager->expects($this->once())->method('remove');
        $entityManager->expects($this->once())->method('flush');

        $service = new CategoryService($entityManager, $repo);
        $category = new Category();
        $service->delete($category);
    }

    public function testDeleteThrowsWhenUsed(): void
    {
        $this->expectException(\DomainException::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EventRepository::class);
        $repo->method('count')->willReturn(3);

        $service = new CategoryService($entityManager, $repo);
        $category = new Category();
        $service->delete($category);
    }
}

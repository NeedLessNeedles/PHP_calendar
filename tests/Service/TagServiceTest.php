<?php

/**
 * Tests for TagService.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends TestCase
{
    private TagRepository $tagRepository;

    private PaginatorInterface $paginator;

    private TagService $service;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->tagRepository = $this->createStub(
            TagRepository::class
        );

        $this->paginator = $this->createStub(
            PaginatorInterface::class
        );

        $this->service = new TagService(
            $this->tagRepository,
            $this->paginator
        );
    }

    /**
     * Test for pagination.
     */
    public function testGetPaginatedList(): void
    {
        $queryBuilder = $this->createStub(QueryBuilder::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $paginator = $this->createMock(
            PaginatorInterface::class
        );

        $tagRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $paginator
            ->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                2,
                5,
                [
                    'sortFieldAllowList' => [
                        'tag.id',
                        'tag.title',
                    ],
                    'defaultSortFieldName' => 'tag.id',
                    'defaultSortDirection' => 'desc',
                ]
            )
            ->willReturn($pagination);

        $this->service = new TagService(
            $tagRepository,
            $paginator
        );

        $result = $this->service->getPaginatedList(2);

        $this->assertSame($pagination, $result);
    }

    /**
     * Test for save() method.
     */
    public function testSave(): void
    {
        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $tag = new Tag();

        $tagRepository
            ->expects($this->once())
            ->method('save')
            ->with($tag);

        $this->service = new TagService(
            $tagRepository,
            $this->paginator
        );

        $this->service->save($tag);
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForNullTitle(): void
    {
        $tag = new Tag();

        $this->assertFalse(
            $this->service->canBeEmpty($tag)
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsFalseForBlankTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('   ');

        $this->assertFalse(
            $this->service->canBeEmpty($tag)
        );
    }

    /**
     * Test for canBeEmpty() method.
     */
    public function testCanBeEmptyReturnsTrueForValidTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('Music');

        $this->assertTrue(
            $this->service->canBeEmpty($tag)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsFalseForBlankTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('   ');

        $this->assertFalse(
            $this->service->isTitleUnique($tag)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsTrueWhenTagDoesNotExist(): void
    {
        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $tagRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn(null);

        $this->service = new TagService(
            $tagRepository,
            $this->paginator
        );

        $tag = new Tag();
        $tag->setTitle('Music');

        $this->assertTrue(
            $this->service->isTitleUnique($tag)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsTrueForSameTag(): void
    {
        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $tag = new Tag();
        $tag->setTitle('Music');

        $reflection = new \ReflectionClass($tag);
        $property = $reflection->getProperty('id');
        $property->setValue($tag, 1);

        $tagRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn($tag);

        $this->service = new TagService(
            $tagRepository,
            $this->paginator
        );

        $this->assertTrue(
            $this->service->isTitleUnique($tag)
        );
    }

    /**
     * Test for isTitleUnique() method.
     */
    public function testIsTitleUniqueReturnsFalseForAnotherTag(): void
    {
        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $tag = new Tag();
        $tag->setTitle('Music');

        $existingTag = new Tag();

        $reflection = new \ReflectionClass($tag);
        $property = $reflection->getProperty('id');
        $property->setValue($tag, 1);

        $reflection = new \ReflectionClass($existingTag);
        $property = $reflection->getProperty('id');
        $property->setValue($existingTag, 2);

        $tagRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['title' => 'Music'])
            ->willReturn($existingTag);

        $this->service = new TagService(
            $tagRepository,
            $this->paginator
        );

        $this->assertFalse(
            $this->service->isTitleUnique($tag)
        );
    }

    /**
     * Test if tag can be deleted.
     */
    public function testDelete(): void
    {
        $tagRepository = $this->createMock(
            TagRepository::class
        );

        $tag = new Tag();

        $tagRepository
            ->expects($this->once())
            ->method('delete')
            ->with($tag);

        $this->service = new TagService(
            $tagRepository,
            $this->paginator
        );

        $this->service->delete($tag);
    }
}

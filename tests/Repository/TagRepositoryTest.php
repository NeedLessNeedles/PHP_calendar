<?php

/**
 * Tests for TagRepository.
 */

namespace App\Tests\Repository;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagRepositoryTest.
 */
class TagRepositoryTest extends KernelTestCase
{
    private TagRepository $tagRepository;

    /**
     * Constructor test.
     */
    public function testRepositoryCanBeCreated(): void
    {
        self::bootKernel();
        $repo = self::getContainer()->get(TagRepository::class);

        $this->assertInstanceOf(TagRepository::class, $repo);
    }

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->tagRepository = self::getContainer()->get(TagRepository::class);
    }

    /**
     * Test Repository setup.
     */
    public function testRepositoryFromSetUp(): void
    {
        $this->assertInstanceOf(TagRepository::class, $this->tagRepository);
    }

    /**
     * Test query builder.
     */
    public function testQueryAll(): void
    {
        $qb = $this->tagRepository->queryAll();

        $this->assertStringContainsString(
            'SELECT',
            $qb->getDQL()
        );

        $this->assertStringContainsString(
            'FROM App\Entity\Tag',
            $qb->getDQL()
        );
    }

    /**
     * Test saving tag.
     */
    public function testSave(): void
    {
        $tag = new Tag();
        $tag->setTitle(
            'Repository test tag'
        );

        $this->tagRepository->save($tag);
        $this->assertNotNull($tag->getId());
        $savedTag = $this->tagRepository->find(
            $tag->getId()
        );

        $this->assertInstanceOf(
            Tag::class,
            $savedTag
        );
        $this->assertSame(
            'Repository test tag',
            $savedTag->getTitle()
        );
    }

    /**
     * Test deleting tag.
     */
    public function testDelete(): void
    {
        $tag = new Tag();
        $tag->setTitle(
            'Repository tag to delete'
        );

        $this->tagRepository->save($tag);
        $tagId = $tag->getId();
        $this->assertNotNull($tagId);
        $this->tagRepository->delete($tag);

        $deletedTag = $this->tagRepository->find(
            $tagId
        );

        $this->assertNull($deletedTag);
    }
}

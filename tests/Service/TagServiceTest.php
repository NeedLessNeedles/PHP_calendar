<?php

/**
 * Tests for TagService.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Service\TagService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends TestCase
{
    public function testEdit(): void
    {
        $tag = new Tag();
        $tag->setTitle('old');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new TagService($entityManager);
        $service->edit($tag, 'new-title');

        $this->assertSame('new-title', $tag->getTitle());
    }

    public function testDelete(): void
    {
        $tag = new Tag();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($tag);
        $entityManager
            ->expects($this->once())
            ->method('flush');
        $service = new TagService($entityManager);

        $service->delete($tag);
    }
}

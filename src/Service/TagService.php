<?php

/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagService.
 */
class TagService implements TagServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function edit(Tag $tag, string $title): void
    {
        $tag->setTitle($title);
    }

    public function delete(Tag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }
}

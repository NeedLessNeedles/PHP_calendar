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
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager Entity manager
     */
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Edit tag.
     *
     * @param Tag    $tag   Tag
     * @param string $title Title
     */
    public function edit(Tag $tag, string $title): void
    {
        $tag->setTitle($title);
    }

    /**
     * Delete tag.
     *
     * @param Tag $tag Tag
     */
    public function delete(Tag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }
}

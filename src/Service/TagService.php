<?php

/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TagService.
 */
class TagService implements TagServiceInterface
{
    public const PAGINATOR_ITEMS_PER_PAGE = 5;
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager Entity manager
     */
    public function __construct(private readonly TagRepository $tagRepository, private readonly PaginatorInterface $paginator, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int         $page       Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->tagRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['tag.id'],
                'defaultSortFieldName' => 'tag.id',
                'defaultSortDirection' => 'desc',
            ]
        );
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

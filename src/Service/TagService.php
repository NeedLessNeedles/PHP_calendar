<?php

/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Category;
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
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 5;

    /**
     * Constructor.
     *
     * @param TagRepository          $tagRepository Tag repository
     * @param PaginatorInterface     $paginator     Paginator
     * @param EntityManagerInterface $entityManager Entity manager
     */
    public function __construct(private readonly TagRepository $tagRepository, private readonly PaginatorInterface $paginator, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
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
                'sortFieldAllowList' => ['tag.id', 'tag.title'],
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
     * Save entity.
     *
     * @param Tag $tag Tag entity
     */
    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }

    /**
     * Delete entity.
     *
     * @param Tag $tag Tag
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }
}

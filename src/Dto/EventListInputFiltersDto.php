<?php
/**
 * Event list input filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;

/**
 * Class EventListInputFiltersDto.
 */
class EventListInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null $categoryId Category identifier
     * @param int      $statusId   Status identifier
     */
    public function __construct(public readonly ?int $categoryId = null, public readonly int $statusId = 1)
    {
    }
}

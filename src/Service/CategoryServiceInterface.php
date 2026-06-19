<?php
/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    public function edit(Category $category, string $title): void;
}

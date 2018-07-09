<?php

namespace App\Repositories\Category;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface LinkRepository.
 *
 * @package namespace App\Repositories\Category;
 */
interface LinkRepository extends RepositoryInterface
{
    public function incrementCurrentPage($id);
}

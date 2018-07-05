<?php

namespace App\Repositories\Product;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Product\AsinRepository;
use App\Entities\Product\Asin;

/**
 * Class LinkRepositoryEloquent.
 *
 * @package namespace App\Repositories\Product;
 */
class AsinRepositoryEloquent extends BaseRepository implements AsinRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Asin::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}

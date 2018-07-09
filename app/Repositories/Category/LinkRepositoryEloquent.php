<?php

namespace App\Repositories\Category;

use App\Entities\Category\Link;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class LinkRepositoryEloquent.
 *
 * @package namespace App\Repositories\Category;
 */
class LinkRepositoryEloquent extends BaseRepository implements LinkRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Link::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Increment current_page value.
     *
     * @param $id
     */
    public function incrementCurrentPage($id)
    {
        $item = $this->find($id);

        try {
            $this->update([
                'current_page' => (int)$item['current_page'] + 1
            ], $id);
        } catch (ValidatorException $e) {
            Log::error('Could not increment a current page!', [
                'item_id' => $id,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'class' => get_class($this),
                'line' => $e->getLine()
            ]);
        }
    }
}

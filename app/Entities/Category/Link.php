<?php

namespace App\Entities\Category;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Link.
 *
 * @package namespace App\Entities\Category;
 */
class Link extends Model implements Transformable
{
    protected $table = 'category_link';

    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['url', 'current_page', 'last_page', 'completed'];

}

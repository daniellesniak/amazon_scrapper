<?php

namespace App\Entities\Product;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Link.
 *
 * @package namespace App\Entities\Product;
 */
class Link extends Model implements Transformable
{
    protected $table = 'products_links';

    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['url', 'is_crawled', 'crawled_at'];

}

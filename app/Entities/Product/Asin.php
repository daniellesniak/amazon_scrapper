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
class Asin extends Model implements Transformable
{
    protected $table = 'asin_numbers';

    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['asin', 'is_crawled', 'crawled_at'];

}

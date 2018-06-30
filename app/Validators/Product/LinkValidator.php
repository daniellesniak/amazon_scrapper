<?php

namespace App\Validators\Product;

use Prettus\Validator\LaravelValidator;

class LinkValidator extends LaravelValidator
{
    protected $rules = [
        'url' => 'required|url'
    ];
}
<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \Whilesmart\Reviews\Traits\Reviewable;

    protected $guarded = [];
}

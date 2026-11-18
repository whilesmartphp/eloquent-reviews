<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Reviews\Traits\Reviewable;

class Product extends Model
{
    use Reviewable;

    protected $guarded = [];
}

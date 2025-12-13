<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Define the class expected by the Morph Map and Review model's relation
class ReviewableDummy extends Model
{
    protected $table = 'reviewable_dummies';
}
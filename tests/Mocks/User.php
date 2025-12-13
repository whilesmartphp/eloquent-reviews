<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Define the class expected by the Review model's relationship method
// No inheritance needed, just definition.
class User extends Model
{
    protected $table = 'users';
}
    // The class definition is enough to satisfy the autoloader.

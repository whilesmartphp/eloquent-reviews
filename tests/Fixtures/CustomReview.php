<?php

namespace Whilesmart\Reviews\Tests\Fixtures;

use Whilesmart\Reviews\Models\Review;

class CustomReview extends Review
{
    protected $appends = ['custom_model'];

    public function getCustomModelAttribute(): bool
    {
        return true;
    }
}

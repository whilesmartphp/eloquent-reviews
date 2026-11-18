<?php

namespace Whilesmart\Reviews\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Whilesmart\Reviews\Models\Review;

class ReviewCreated implements ShouldDispatchAfterCommit
{
    public function __construct(public readonly Review $review) {}
}

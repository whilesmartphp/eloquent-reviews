<?php

namespace Whilesmart\Reviews\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Whilesmart\Reviews\Models\ReviewRevision;

class ReviewRevisionCreated implements ShouldDispatchAfterCommit
{
    public function __construct(public readonly ReviewRevision $revision) {}
}

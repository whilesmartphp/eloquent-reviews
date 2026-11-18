<?php

namespace Whilesmart\Reviews\Tests\Fixtures;

use Whilesmart\Reviews\Models\ReviewRevision;

class CustomRevision extends ReviewRevision
{
    protected $appends = ['custom_model'];

    public function getCustomModelAttribute(): bool
    {
        return true;
    }
}

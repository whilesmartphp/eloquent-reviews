<?php

namespace Whilesmart\Reviews\Exceptions;

use RuntimeException;

class ReviewRevisionConflict extends RuntimeException
{
    public function __construct(public readonly int $expectedRevision, public readonly int $currentRevision)
    {
        parent::__construct('The review revision changed. Refresh before saving.');
    }
}

<?php

use Whilesmart\Reviews\Models\Review;
use Whilesmart\Reviews\Models\ReviewRevision;

return [
    'table' => 'reviews',
    'string_identifiers' => false,
    'revisions_table' => 'review_revisions',
    'models' => [
        'review' => Review::class,
        'revision' => ReviewRevision::class,
    ],
];

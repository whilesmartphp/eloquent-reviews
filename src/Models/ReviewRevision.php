<?php

namespace Whilesmart\Reviews\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class ReviewRevision extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = ['revision' => 'integer', 'data' => 'array', 'changes' => 'array', 'created_at' => 'datetime'];

    public function getTable(): string
    {
        return config('reviews.revisions_table', 'review_revisions');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Review revisions cannot be updated.'));
        static::deleting(fn () => throw new LogicException('Review revisions cannot be deleted.'));
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): MorphTo
    {
        return $this->morphTo();
    }
}

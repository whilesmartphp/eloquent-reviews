<?php

namespace Whilesmart\Reviews\Models;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Reviews\Enums\ReviewStatus;

class Review extends Model
{
    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'reviewer_type',
        'reviewer_id',
        'status',
        'notes',
        'reviewed_at',
        'metadata',
    ];

    protected $casts = [
        'status' => ReviewStatus::class,
        'reviewed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function accept(): void
    {
        $this->status = ReviewStatus::ACCEPTED;
        $this->save();
    }

    public function reject(): void
    {
        $this->status = ReviewStatus::REJECTED;
        $this->save();
    }

    public function isAccepted(): bool
    {
        return $this->status === ReviewStatus::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === ReviewStatus::REJECTED;
    }
}

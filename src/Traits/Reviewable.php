<?php

namespace Whilesmart\Reviews\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Whilesmart\Reviews\Models\Review;

trait Reviewable
{
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function addReview(?Model $reviewer, ReviewStatus $status = ReviewStatus::PENDING, ?string $notes = null, ?array $metadata = null): Review
    {
        return $this->reviews()->create([
            'reviewer_id' => $reviewer?->getKey(),
            'reviewer_type' => $reviewer?->getMorphClass(), // Automatically gets the class name
            'status' => $status->value,
            'notes' => $notes,
            'reviewed_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    public function isReviewed(): bool
    {
        return $this->reviews()->exists();
    }
}

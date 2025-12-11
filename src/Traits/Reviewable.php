<?php

namespace Whilesmart\Reviews\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Whilesmart\Reviews\Models\Review;

trait Reviewable
{
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function latestReview(): MorphOne
    {
        return $this->morphOne(Review::class, 'reviewable')->latestOfMany();
    }

    public function addReview(int $reviewerId, string $status, ?string $notes = null, ?array $metadata = null): Review
    {
        return $this->reviews()->create([
            'reviewer_id' => $reviewerId,
            'status' => $status,
            'notes' => $notes,
            'reviewed_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    public function accept(int $reviewerId, ?string $notes = null, ?array $metadata = null): Review
    {
        return $this->addReview($reviewerId, 'accepted', $notes, $metadata);
    }

    public function reject(int $reviewerId, string $notes, ?array $metadata = null): Review
    {
        return $this->addReview($reviewerId, 'rejected', $notes, $metadata);
    }

    public function isReviewed(): bool
    {
        return $this->reviews()->exists();
    }

    public function isAccepted(): bool
    {
        return $this->latestReview?->isAccepted() ?? false;
    }

    public function isRejected(): bool
    {
        return $this->latestReview?->isRejected() ?? false;
    }

    public function getReviewStatus(): ?string
    {
        return $this->latestReview?->status;
    }

    public function getReviewNotes(): ?string
    {
        return $this->latestReview?->notes;
    }
}

<?php

namespace Whilesmart\Reviews\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Event;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Whilesmart\Reviews\Events\ReviewCreated;
use Whilesmart\Reviews\Models\Review;
use Whilesmart\Reviews\Models\ReviewRevision;
use Whilesmart\Reviews\Services\ReviewRevisionService;
use Whilesmart\Reviews\Support\ReviewModels;

trait Reviewable
{
    public function reviews(): MorphMany
    {
        return $this->morphMany(ReviewModels::review(), 'reviewable');
    }

    public function addReview(?Model $reviewer, ReviewStatus $status = ReviewStatus::PENDING, ?string $notes = null, ?array $metadata = null): Review
    {
        $review = $this->reviews()->create([
            'reviewer_id' => $reviewer?->getKey(),
            'reviewer_type' => $reviewer?->getMorphClass(),
            'status' => $status->value,
            'notes' => $notes,
            'reviewed_at' => now(),
            'metadata' => $metadata,
        ]);
        Event::dispatch(new ReviewCreated($review));

        return $review;
    }

    public function latestReview(): MorphOne
    {
        return $this->morphOne(ReviewModels::review(), 'reviewable')->latestOfMany();
    }

    public function reviewRevisions(): MorphMany
    {
        return $this->morphMany(ReviewModels::revision(), 'reviewable');
    }

    public function latestReviewRevision(): MorphOne
    {
        return $this->morphOne(ReviewModels::revision(), 'reviewable')->latestOfMany('revision');
    }

    public function appendReviewRevision(?Model $reviewer, int $expectedRevision, array $changes, array $initialData = [], ?string $notes = null): ReviewRevision
    {
        return app(ReviewRevisionService::class)->append($this, $reviewer, $expectedRevision, $changes, $initialData, $notes);
    }

    public function isReviewed(): bool
    {
        return $this->reviews()->exists();
    }
}

<?php

namespace Whilesmart\Reviews\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Whilesmart\Reviews\Events\ReviewRevisionCreated;
use Whilesmart\Reviews\Exceptions\ReviewRevisionConflict;
use Whilesmart\Reviews\Models\ReviewRevision;

class ReviewRevisionService
{
    public function append(Model $subject, ?Model $reviewer, int $expectedRevision, array $changes, array $initialData = [], ?string $notes = null): ReviewRevision
    {
        if ($expectedRevision < 0 || $changes === []) {
            throw new InvalidArgumentException('Provide a non-negative expected revision and at least one change.');
        }

        return $subject->getConnection()->transaction(function () use ($subject, $reviewer, $expectedRevision, $changes, $initialData, $notes) {
            $subject = $subject->newQuery()->whereKey($subject->getKey())->lockForUpdate()->firstOrFail();
            $previous = $subject->reviewRevisions()->orderByDesc('revision')->lockForUpdate()->first();
            $current = $previous?->revision ?? 0;
            if ($current !== $expectedRevision) {
                throw new ReviewRevisionConflict($expectedRevision, $current);
            }
            $revision = $subject->reviewRevisions()->create([
                'revision' => $current + 1,
                'data' => array_replace($previous?->data ?? $initialData, $changes),
                'changes' => $changes,
                'reviewer_type' => $reviewer?->getMorphClass(),
                'reviewer_id' => $reviewer?->getKey(),
                'notes' => $notes,
            ]);
            Event::dispatch(new ReviewRevisionCreated($revision));

            return $revision;
        }, 3);
    }
}

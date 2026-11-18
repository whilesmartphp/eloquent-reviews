# API reference

## Reviewable

Add `Whilesmart\Reviews\Traits\Reviewable` to a subject model.

| Method | Result |
| --- | --- |
| `addReview(?Model $reviewer, ReviewStatus $status = ReviewStatus::PENDING, ?string $notes = null, ?array $metadata = null)` | Creates and returns a review decision |
| `reviews()` | Polymorphic collection relation using the configured review model |
| `latestReview()` | Latest decision by its numeric review ID |
| `isReviewed()` | Whether any decision exists |
| `appendReviewRevision(?Model $reviewer, int $expectedRevision, array $changes, array $initialData = [], ?string $notes = null)` | Appends and returns a correction snapshot |
| `reviewRevisions()` | Polymorphic collection relation using the configured revision model |
| `latestReviewRevision()` | Snapshot with the greatest revision number |

The default review status enum is `Whilesmart\Reviews\Enums\ReviewStatus` with cases `PENDING`, `ACCEPTED`, `REJECTED`, and `FLAGGED`.

## Review decisions

`Whilesmart\Reviews\Models\Review` exposes polymorphic `reviewable()` and `reviewer()` relations. `accept()` and `reject()` update the decision's status. `isAccepted()` and `isRejected()` inspect it. The `metadata` attribute casts to an array.

## Review revisions

`Whilesmart\Reviews\Models\ReviewRevision` exposes `revision`, complete snapshot `data`, submitted `changes`, reviewer references, notes and creation time. It also exposes `reviewable()` and `reviewer()` relations. In-place Eloquent updates and deletes throw `LogicException`.

`ReviewRevisionConflict` exposes `expectedRevision` and `currentRevision` when the caller edits a stale snapshot. See [review history](review-history.md) for transactions, events and upgrade behavior.

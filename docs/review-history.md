# Review history and customization

Review decisions and data corrections are separate records. `addReview()` creates a decision whose status can still be accepted or rejected. `appendReviewRevision()` records a new snapshot without changing earlier revisions.

## Configuration

Publish configuration with `php artisan vendor:publish --tag=reviews-config`.

`reviews.models.review` must extend `Whilesmart\Reviews\Models\Review`. `reviews.models.revision` must extend `Whilesmart\Reviews\Models\ReviewRevision`. Relationships resolve these classes when they are used. Invalid classes raise `InvalidArgumentException`.

`reviews.table` defaults to `reviews`; `reviews.revisions_table` defaults to `review_revisions`. Set custom names before the first migration. Moving an existing table requires an application migration.

## Identifiers and upgrades

Existing reviews keep Laravel's configured morph key strategy by default. Set `reviews.string_identifiers` to `true` before running migrations to support a mixture of numeric, UUID and ULID subjects and reviewers in the same review table. This stores identifiers as strings, including numeric ones.

The upgrade migration widens existing review identifiers when this option is enabled. Existing numeric references remain usable. If the option is enabled after that migration has already run, call `Whilesmart\Reviews\Support\ReviewSchema::useStringIdentifiers()` from an application migration. Host-owned foreign keys must be removed or adapted before widening their columns.

Revision identifiers always use strings. Rolling back the revision migration drops its table but retains widened review identifiers, so existing string references are not narrowed or discarded.

## Latest decisions

Models using `Reviewable` expose `reviews()` and `latestReview()`. Both use the configured review model. Reviews expose polymorphic `reviewable()` and `reviewer()` relations. Anonymous reviews have a null reviewer.

```php
$review = $evidence->addReview($user, ReviewStatus::ACCEPTED, 'Checked');
$latest = $evidence->latestReview;
```

## Correction snapshots

```php
$revision = $evidence->appendReviewRevision(
    reviewer: $user,
    expectedRevision: 0,
    changes: ['amount' => 125],
    initialData: ['amount' => 120, 'reference' => 'R-1'],
    notes: 'Checked the source',
);
```

The first revision starts at one. Top-level changes are merged with the initial data for that revision, then with the previous snapshot for later revisions. `data` contains the complete snapshot; `changes` contains the submitted patch. `reviewRevisions()` returns history and `latestReviewRevision()` returns the greatest revision number.

The service locks the subject and reads the latest committed revision before applying changes. It also supports calls made inside an application's existing transaction. A stale expected revision throws `ReviewRevisionConflict`, which exposes `expectedRevision` and `currentRevision`. HTTP consumers can map this exception to a 409 response and refresh before retrying.

In-place Eloquent updates and deletes on revision models throw `LogicException`. Bulk query writes and direct SQL are outside that model guard. Applications remain responsible for authorization, field validation, retention and protection of database access. The package does not register production HTTP routes.

## Events

`ReviewCreated` carries the new `review`. `ReviewRevisionCreated` carries the new `revision`. Both dispatch after the surrounding transaction commits and are discarded on rollback.

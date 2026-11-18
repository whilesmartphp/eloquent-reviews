# WhileSmart Reviews

Attach review decisions and correction history to Laravel models.

```sh
composer require whilesmart/reviews
php artisan migrate
```

Add `Whilesmart\Reviews\Traits\Reviewable` to a model. Create a decision with a reviewer model and a `ReviewStatus` enum:

```php
use Whilesmart\Reviews\Enums\ReviewStatus;

$review = $evidence->addReview($user, ReviewStatus::ACCEPTED, 'Checked');
$latest = $evidence->latestReview;
```

A reviewer can be any Eloquent model or null for an anonymous review. `reviews()` returns the subject's decisions. Reviews expose `reviewable()` and `reviewer()` relations.

For data corrections, append a snapshot with the revision number the caller last read:

```php
$revision = $evidence->appendReviewRevision(
    reviewer: $user,
    expectedRevision: 0,
    changes: ['amount' => 125],
    initialData: ['amount' => 120, 'reference' => 'R-1'],
);
```

Stale edits throw `ReviewRevisionConflict`. Earlier revision models reject updates and deletes. Applications provide authorization, field validation and HTTP responses; the package does not register production routes.

See [getting started](docs/getting-started.md), [configuration](docs/configuration.md), [API reference](docs/api-reference.md), and [review history](docs/review-history.md) for customization and upgrade requirements.

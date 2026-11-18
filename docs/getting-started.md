# Getting started

Install the package and create its tables:

```sh
composer require whilesmart/reviews
php artisan migrate
```

Add `Whilesmart\Reviews\Traits\Reviewable` to the Eloquent model that receives reviews. Pass an Eloquent reviewer model and a `Whilesmart\Reviews\Enums\ReviewStatus` value to `addReview()`. The status values are `pending`, `accepted`, `rejected`, and `flagged`.

```php
use Whilesmart\Reviews\Enums\ReviewStatus;

$review = $evidence->addReview($user, ReviewStatus::PENDING, 'Needs checking');
$review->accept();
```

The reviewer may be null for an anonymous decision. Decisions are mutable; use [review revisions](review-history.md) when earlier snapshots must remain unchanged.

Applications authorize reviewers and expose their own HTTP endpoints. See [configuration](configuration.md) for model and identifier options, and the [API reference](api-reference.md) for method signatures.

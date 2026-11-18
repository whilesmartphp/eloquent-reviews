# Configuration

Publish the package configuration:

```sh
php artisan vendor:publish --tag=reviews-config
```

| Option | Default | Purpose |
| --- | --- | --- |
| `table` | `reviews` | Decision table |
| `revisions_table` | `review_revisions` | Correction snapshot table |
| `models.review` | `Whilesmart\Reviews\Models\Review` | Review subclass used by relations |
| `models.revision` | `Whilesmart\Reviews\Models\ReviewRevision` | Revision subclass used by relations |
| `string_identifiers` | `false` | Opt into mixed numeric, UUID and ULID decision identifiers |

Set table names and identifier options before migrating. Custom models must extend the corresponding package model. They can add relations, casts and serialization without replacing the package trait.

See [identifier upgrades and review history](review-history.md) before changing an existing installation.

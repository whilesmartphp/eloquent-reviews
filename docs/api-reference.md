# API Reference

### `Reviewable` Trait
The primary trait to enable reviews on your Eloquent models.

#### `createReview(array $data)`
Creates a new review associated with the model.
- **$data['status']**: string
- **$data['title']**: String
- **$data['body']**: String

#### `reviews()`
Returns a polymorphic `MorphMany` relationship of the reviews.
<?php

namespace Whilesmart\Reviews\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Whilesmart\Reviews\Models\Review;
use Whilesmart\Reviews\Tests\TestCase;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_a_model_can_be_reviewed_by_a_reviewer()
    {
        $user = User::create(['name' => 'iMercy', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
        $product = Product::create(['title' => 'Smart Watch']);

        // Act: Using the signature c
        $product->addReview(reviewer: $user, status: ReviewStatus::PENDING, notes: 'Great watch!');

        // Assert
        $this->assertDatabaseHas('reviews', [
            'reviewable_id' => $product->id,
            'reviewer_id' => $user->id,
            'status' => ReviewStatus::PENDING->value,
            'notes' => 'Great watch!',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_a_model_can_receive_an_anonymous_review()
    {
        $product = Product::create(['title' => 'Anonymous Product']);

        // Act: reviewer is null/omitted
        $product->addReview(reviewer: null, status: ReviewStatus::PENDING, notes: 'Left by a guest');

        $this->assertDatabaseHas('reviews', [
            'reviewable_id' => $product->id,
            'reviewer_id' => null,
            'notes' => 'Left by a guest',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_a_review_can_be_accepted()
    {
        $product = Product::create(['title' => 'Logic Test']);
        $review = $product->addReview(null, notes: 'Pending Review');

        // Act: Use the action method on the Review model
        $review->accept();

        // Assert
        $this->assertTrue($review->isAccepted());
        $this->assertEquals(ReviewStatus::ACCEPTED, $review->status);

        // Verify the parent model (Product) also reflects this as the latest state
        $this->assertTrue($product->fresh()->isAccepted());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_a_review_can_be_rejected_with_notes()
    {
        $product = Product::create(['title' => 'Logic Test']);
        $review = $product->addReview(null, notes: 'Initial Notes');

        // Act
        $review->reject();

        // Assert
        $this->assertTrue($review->isRejected());
        $this->assertEquals(ReviewStatus::REJECTED, $review->status);
        $this->assertTrue($product->fresh()->isRejected());
    }
}

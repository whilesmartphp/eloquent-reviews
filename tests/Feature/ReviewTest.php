<?php

namespace Whilesmart\Reviews\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Whilesmart\Reviews\Models\Review;
use Whilesmart\Reviews\Tests\TestCase;
use Whilesmart\Reviews\Traits\Reviewable;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_a_product_can_receive_a_review_from_a_user()
    {
        $user = User::create(['name' => 'iMercy', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
        $product = Product::create(['title' => 'Smart Watch']);

        // Act: Create a review using the trait (default status is PENDING)
        $product->addReview($user, notes: 'Great watch!');

        // Assert: Check creation
        $this->assertDatabaseHas('reviews', [
            'reviewable_id' => $product->id,
            'reviewer_id' => $user->id,
            'status' => ReviewStatus::PENDING->value,
            'notes' => 'Great watch!',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_a_product_can_receive_an_anonymous_review()
    {
        $product = Product::create(['title' => 'Anonymous Product']);

        // Act: Pass null for the reviewer
        $product->addReview(null, notes: 'Left by a guest');

        // Assert: reviewer_id and reviewer_type should be null
        $this->assertDatabaseHas('reviews', [
            'reviewable_id' => $product->id,
            'reviewer_id' => null,
            'reviewer_type' => null,
            'notes' => 'Left by a guest',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_an_existing_review_can_be_accepted_via_model_method()
    {
        $product = Product::create(['title' => 'Logic Test']);
        $review = $product->addReview(null, notes: 'Pending Review');

        // Act: Call the new action method on the Reviewable Model
        $review->accept();
        $product->refresh();

        // Assert: Status updated to ACCEPTED
        $this->assertEquals(ReviewStatus::ACCEPTED, $review->status);
        $this->assertTrue($product->isAccepted());

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => ReviewStatus::ACCEPTED->value,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_an_existing_review_can_be_rejected_via_model_method()
    {
        $product = Product::create(['title' => 'Logic Test']);
        $review = $product->addReview(null, notes: 'Bad Review');

        // Act: Call the new action method on the Review Model
        $review->reject();

        // Assert: Status updated to REJECTED
        $this->assertEquals(ReviewStatus::REJECTED, $review->status);
        $this->assertTrue($product->isRejected());
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => ReviewStatus::REJECTED->value,
        ]);
    }
}

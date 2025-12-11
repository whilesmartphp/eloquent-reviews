<?php

namespace Whilesmart\Reviews\Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase;
use Whilesmart\Reviews\Models\Review;

#[WithMigration]
class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function createReview(array $attributes = []): Review
    {
        return Review::create(array_merge([
            'reviewable_type' => 'SomeModel', // Dummy value for polymorphic relation
            'reviewable_id' => 1,               // Dummy ID
            'reviewer_id' => 1,                  // Dummy reviewer ID
            'status' => 'pending',
            'notes' => null,
            'reviewed_at' => null,
            'metadata' => null,
        ], $attributes));
    }

    public function test_create_review()
    {
        $review = $this->createReview([
            'reviewer_id' => 2,
            'status' => 'accepted',
            'notes' => 'Valid review.',
        ]);

        $this->assertDatabaseHas('reviews', [
            'reviewer_id' => 2,
            'status' => 'accepted',
            'notes' => 'Valid review.',
        ]);
        $this->assertNotNull($review->reviewed_at);
    }

    public function test_review_status_scopes()
    {
        $this->createReview(['status' => 'accepted']);
        $this->createReview(['status' => 'rejected']);
        $this->createReview(['status' => 'pending']);

        $this->assertEquals(1, Review::accepted()->count());
        $this->assertEquals(1, Review::rejected()->count());
        $this->assertEquals(1, Review::pending()->count());
    }

    public function test_review_status_methods()
    {
        $review = $this->createReview(['status' => 'accepted']);
        
        $this->assertTrue($review->isAccepted());
        $this->assertFalse($review->isRejected());
        $this->assertFalse($review->isPending());
    }

    public function test_review_metadata_handling()
    {
        $review = $this->createReview(['metadata' => ['key' => 'value']]);
        
        $this->assertEquals(['key' => 'value'], $review->metadata);
    }

    public function test_can_update_review_status()
    {
        $review = $this->createReview(['status' => 'pending']);
        
        $review->status = 'accepted';
        $review->save();

        $this->assertTrue($review->isAccepted());
        $this->assertEquals('accepted', $review->status);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Whilesmart\Reviews\ReviewsServiceProvider::class,
        ];
    }
}
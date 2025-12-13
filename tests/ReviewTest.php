<?php

namespace Whilesmart\Reviews\Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Whilesmart\Reviews\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
#[WithMigration]
class ReviewTest extends TestCase
{
    protected $faker;
    protected $reviewerId = 10;
    protected $reviewableId = 20;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->createDependencies();
    }

    protected function createDependencies(): void
    {
        // 1. Ensure Reviewer (User) exists
        if (!DB::table('users')->where('id', $this->reviewerId)->exists()) {
            DB::table('users')->insert(['id' => $this->reviewerId, 'name' => 'Test Reviewer', 'created_at' => now(), 'updated_at' => now()]);
        }
        
        // 2. Ensure Reviewable host exists
        if (!DB::table('reviewable_dummies')->where('id', $this->reviewableId)->exists()) {
            DB::table('reviewable_dummies')->insert(['id' => $this->reviewableId, 'name' => 'Test Item', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    /**
     * Helper to create a Review record using the mocked dependencies.
     */
    protected function createReview(array $attributes = []): Review
    {
        return Review::create(array_merge([
            'reviewable_type' => 'reviewable_dummy',
            'reviewable_id' => $this->reviewableId,
            'reviewer_id' => $this->reviewerId,
            'status' => 'pending',
            'notes' => null,
            'reviewed_at' => null,
            'metadata' => null,
        ], $attributes));
    }


    // =======================================================================
    // I. Attribute and Casts Tests
    // =======================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_and_persists_metadata_as_array()
    {
        $metadata = ['key' => $this->faker->word, 'version' => 3, 'data' => ['a', 'b']];
        $review = $this->createReview(['metadata' => $metadata]);

        // 1. Check Model Property Cast
        $this->assertIsArray($review->metadata);
        $this->assertEquals($metadata, $review->metadata);
        
        // 2. Check Database Storage (stored as JSON string)
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'metadata' => json_encode($metadata),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_reviewed_at_to_carbon_datetime()
    {
        $time = now()->subMinutes(5);
        $review = $this->createReview(['reviewed_at' => $time]);
        
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $review->reviewed_at);
        $this->assertEquals($time->getTimestamp(), $review->reviewed_at->getTimestamp());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_minimum_required_fields()
    {
        $review = $this->createReview();
        
        $this->assertInstanceOf(Review::class, $review);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'reviewable_type' => 'reviewable_dummy',
            'reviewer_id' => $this->reviewerId,
            'status' => 'pending',
        ]);
        $this->assertNull($review->notes);
    }
    
    // =======================================================================
    // II. Scope and Getter Tests
    // =======================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_the_accepted_scope()
    {
        $this->createReview(['status' => 'accepted']);
        $this->createReview(['status' => 'rejected']);
        $this->createReview(['status' => 'pending']);

        $this->assertEquals(1, Review::accepted()->count());
        $this->assertEquals('accepted', Review::accepted()->first()->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_the_rejected_scope()
    {
        $this->createReview(['status' => 'accepted']);
        $this->createReview(['status' => 'rejected']);
        $this->createReview(['status' => 'pending']);

        $this->assertEquals(1, Review::rejected()->count());
        $this->assertEquals('rejected', Review::rejected()->first()->status);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_the_pending_scope()
    {
        $this->createReview(['status' => 'accepted']);
        $this->createReview(['status' => 'rejected']);
        $this->createReview(['status' => 'pending']);

        $this->assertEquals(1, Review::pending()->count());
        $this->assertEquals('pending', Review::pending()->first()->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_correctly_uses_the_is_status_methods()
    {
        $reviewAccepted = $this->createReview(['status' => 'accepted']);
        $reviewRejected = $this->createReview(['status' => 'rejected']);
        $reviewPending = $this->createReview(['status' => 'pending']);
        
        $this->assertTrue($reviewAccepted->isAccepted());
        $this->assertFalse($reviewAccepted->isPending());
        $this->assertFalse($reviewAccepted->isRejected());
        
        $this->assertTrue($reviewRejected->isRejected());
        $this->assertTrue($reviewPending->isPending());
    }

    // =======================================================================
    // III. Relationship Tests (Needs Mock Classes)
    // =======================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_the_reviewer_belongs_to_relation()
    {
        $review = $this->createReview();

        // // Temporarily mock the User model class for the relation to resolve
        // // The foreign key constraint is satisfied by the helper.
        // $mockUser = new class extends Model {
        //     protected $table = 'users';
        // };

        // // Bind the mock class to the App\Models\User FQCN expected by the Review model
        // $this->app->instance('App\Models\User', $mockUser); 

        $reviewer = $review->reviewer;
        
        // $this->assertInstanceOf(Model::class, $reviewer); // We expect a Model instance
        $this->assertInstanceOf(\App\Models\User::class, $reviewer);
        $this->assertEquals($this->reviewerId, $reviewer->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_the_reviewable_polymorphic_relation()
    {
        $review = $this->createReview();

        // // Temporarily mock the ReviewableDummy model class for the relation to resolve
        // $mockReviewable = new class extends Model {
        //     protected $table = 'reviewable_dummies';
        // };

        // // Bind the mock class to the App\Models\ReviewableDummy FQCN expected by the MorphMap
        // $this->app->instance('App\Models\ReviewableDummy', $mockReviewable);

        $reviewable = $review->reviewable;
        
        // $this->assertInstanceOf(Model::class, $reviewable); // We expect a Model instance
        $this->assertInstanceOf(\App\Models\ReviewableDummy::class, $reviewable);
        $this->assertEquals($this->reviewableId, $reviewable->id);
    }
}
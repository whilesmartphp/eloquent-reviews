<?php

namespace Whilesmart\Reviews\Tests\Feature;

use Whilesmart\Reviews\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends TestCase
{
    use RefreshDatabase; // Resets the database after every test

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_model_can_be_accepted_by_a_reviewer()
    {
        // 1. Create the data
        $user = User::create(['name' => 'iMercy', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
        $product = Product::create(['title' => 'Smart Watch']);

        // 2. Action: Use the Trait method we built
        $product->accept($user, 'Excellent quality!');

        // 3. Assertions
        $this->assertDatabaseHas('reviews', [
            'reviewable_id' => $product->id,
            'reviewer_id'   => $user->id,
            'status'        => 'accepted',
            'notes'         => 'Excellent quality!'
        ]);

        $this->assertTrue($product->isAccepted());
        $this->assertEquals('accepted', $product->getReviewStatus());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_model_can_be_rejected_with_notes()
    {
        $user = User::create(['name' => 'Reviewer', 'email' => 'rev@example.com', 'password' => bcrypt('password')]);
        $product = Product::create(['title' => 'Broken Toy']);

        $product->reject($user, 'Item was damaged upon arrival.');

        $this->assertEquals('rejected', $product->getReviewStatus());
        $this->assertEquals('Item was damaged upon arrival.', $product->getReviewNotes());
    }
}
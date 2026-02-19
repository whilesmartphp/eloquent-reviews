<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Http\Request;
use Workbench\App\Models\Product;
use Whilesmart\Reviews\Enums\ReviewStatus;

class ReviewController extends \Illuminate\Routing\Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        // Using your Package Trait!
        $review = $product->addReview(
            reviewer: auth()->user(), // Null if guest
            notes: $request->notes,
            status: ReviewStatus::PENDING
        );

        return response()->json([
            'message' => 'Review submitted!',
            'review' => $review
        ]);
    }
}
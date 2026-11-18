<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Workbench\App\Models\Product;

class ReviewController extends Controller
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
            'review' => $review,
        ]);
    }
}

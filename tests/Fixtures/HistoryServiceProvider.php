<?php

namespace Whilesmart\Reviews\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Whilesmart\Reviews\Exceptions\ReviewRevisionConflict;
use Whilesmart\Reviews\Support\ReviewModels;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;

class HistoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        Route::post('/testing/users', function (Request $request) {
            return response()->json(User::create($request->validate(['name' => 'required|string', 'email' => 'required|email', 'password' => 'required|string'])), 201);
        });
        Route::post('/testing/products', function (Request $request) {
            return response()->json(Product::create($request->validate(['title' => 'required|string'])), 201);
        });
        Route::post('/testing/subjects', function (Request $request) {
            return response()->json(UuidSubject::create($request->validate(['title' => 'required|string'])), 201);
        });
        Route::post('/testing/{kind}/{id}/reviews', function (Request $request, string $kind, string $id) {
            $subject = ($kind === 'products' ? Product::class : UuidSubject::class)::findOrFail($id);
            $reviewer = $request->input('reviewer_id') === null ? null
                : ($request->input('reviewer_kind') === 'subject' ? UuidSubject::class : User::class)::findOrFail($request->input('reviewer_id'));
            $review = $subject->addReview($reviewer, ReviewStatus::from($request->input('status', 'pending')), $request->input('notes'));

            return response()->json($review->load(['reviewable', 'reviewer']), 201);
        });
        Route::get('/testing/{kind}/{id}/reviews', function (string $kind, string $id) {
            $subject = ($kind === 'products' ? Product::class : UuidSubject::class)::findOrFail($id);

            return response()->json(['latest' => $subject->latestReview, 'count' => $subject->reviews()->count()]);
        });
        Route::post('/testing/subjects/{id}/revisions', function (Request $request, string $id) {
            return DB::transaction(function () use ($request, $id) {
                $data = $request->validate(['expected_revision' => 'required|integer|min:0', 'changes' => 'required|array|min:1', 'initial_data' => 'sometimes|array', 'notes' => 'sometimes|nullable|string']);
                $subject = UuidSubject::findOrFail($id);
                $reviewer = $request->input('reviewer_id') === null ? null : User::findOrFail($request->input('reviewer_id'));
                try {
                    $revision = $subject->appendReviewRevision($reviewer, $data['expected_revision'], $data['changes'], $data['initial_data'] ?? [], $data['notes'] ?? null);
                } catch (ReviewRevisionConflict $exception) {
                    return response()->json(['current_revision' => $exception->currentRevision], 409);
                }

                return response()->json($revision->load(['reviewer', 'reviewable']), 201);
            });
        });
        Route::get('/testing/subjects/{id}/revisions', function (string $id) {
            $subject = UuidSubject::findOrFail($id);

            return response()->json(['latest' => $subject->latestReviewRevision, 'count' => $subject->reviewRevisions()->count()]);
        });
        Route::patch('/testing/revisions/{id}', function (string $id) {
            try {
                ReviewModels::revision()::findOrFail($id)->update(['notes' => 'Changed']);
            } catch (LogicException) {
                return response()->json(['error' => 'immutable'], 409);
            }

            return response()->json([]);
        });
        Route::delete('/testing/revisions/{id}', function (string $id) {
            try {
                ReviewModels::revision()::findOrFail($id)->delete();
            } catch (LogicException) {
                return response()->json(['error' => 'immutable'], 409);
            }

            return response()->json([]);
        });
    }
}

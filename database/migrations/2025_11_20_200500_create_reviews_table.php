<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Reviews\Enums\ReviewStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('reviews.table', 'reviews'), function (Blueprint $table) {
            $table->id();

            if (config('reviews.string_identifiers', false)) {
                $table->string('reviewable_type');
                $table->string('reviewable_id');
                $table->index(['reviewable_type', 'reviewable_id']);
                $table->string('reviewer_type')->nullable();
                $table->string('reviewer_id')->nullable();
                $table->index(['reviewer_type', 'reviewer_id']);
            } else {
                $table->morphs('reviewable');
                $table->nullableMorphs('reviewer');
            }

            $table->string('status')->default(ReviewStatus::PENDING->value)->index();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable()->index();

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('reviews.table', 'reviews'));
    }
};

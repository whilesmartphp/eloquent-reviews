<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Reviews\Enums\ReviewStatus;
use Whilesmart\Reviews\Models\Review;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship to reviewable (type and id )
            $table->morphs('reviewable');

            // Allow any type of user model even null, incase of anonymity (type and id)
            $table->nullableMorphs('reviewer');

            // Review details
            $table->string('status')->default(ReviewStatus::PENDING->value)->index();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable()->index();

            // Metadata for extensibility
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

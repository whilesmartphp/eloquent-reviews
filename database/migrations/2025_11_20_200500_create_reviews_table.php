<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->enum('status', ['pending', 'accepted', 'rejected', 'flagged'])->default('pending')->index();
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

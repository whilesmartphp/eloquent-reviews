<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Reviews\Support\ReviewSchema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('reviews.string_identifiers', false)) {
            ReviewSchema::useStringIdentifiers();
        }

        Schema::create(config('reviews.revisions_table', 'review_revisions'), function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type');
            $table->string('reviewable_id');
            $table->unsignedInteger('revision');
            $table->json('data');
            $table->json('changes');
            $table->string('reviewer_type')->nullable();
            $table->string('reviewer_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['reviewable_type', 'reviewable_id', 'revision'], 'review_revisions_subject_revision_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('reviews.revisions_table', 'review_revisions'));
    }
};

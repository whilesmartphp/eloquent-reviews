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

            // Polymorphic relationship to reviewable (DocumentSubmission, DocumentVersion, SubmissionBatch, etc.)
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');

            // Reviewer
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');

            // Review details
            $table->enum('status', ['pending', 'accepted', 'rejected', 'flagged'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // Metadata for extensibility
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['reviewable_type', 'reviewable_id']);
            $table->index('reviewer_id');
            $table->index('status');
            $table->index('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');er
    }
};

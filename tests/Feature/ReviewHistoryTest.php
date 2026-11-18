<?php

namespace Whilesmart\Reviews\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Reviews\Events\ReviewCreated;
use Whilesmart\Reviews\Events\ReviewRevisionCreated;
use Whilesmart\Reviews\Tests\Fixtures\CustomReview;
use Whilesmart\Reviews\Tests\Fixtures\CustomRevision;
use Whilesmart\Reviews\Tests\Support\HistoryTestCase;

class ReviewHistoryTest extends HistoryTestCase
{
    public function test_uuid_subjects_and_reviewers_keep_their_relations_and_latest_review(): void
    {
        $subject = $this->subject();
        $reviewer = $this->subject();
        $first = $this->postJson('/testing/subjects/'.$subject.'/reviews', ['reviewer_id' => $reviewer, 'reviewer_kind' => 'subject', 'status' => 'accepted'])
            ->assertCreated()->assertJsonPath('reviewable.id', $subject)->assertJsonPath('reviewer.id', $reviewer)->json('id');
        $second = $this->postJson('/testing/subjects/'.$subject.'/reviews', ['status' => 'rejected'])->assertCreated()->json('id');
        $this->assertNotSame($first, $second);
        $this->getJson('/testing/subjects/'.$subject.'/reviews')->assertOk()->assertJsonPath('latest.id', $second)->assertJsonPath('count', 2);
    }

    public function test_revisions_preserve_snapshots_and_reject_stale_edits(): void
    {
        $subject = $this->subject();
        $reviewer = $this->postJson('/testing/users', ['name' => 'Reviewer', 'email' => 'reviewer@example.test', 'password' => 'testing-password'])->assertCreated()->json('id');
        $path = '/testing/subjects/'.$subject.'/revisions';
        $id = $this->postJson($path, ['expected_revision' => 0, 'changes' => ['total' => 20], 'initial_data' => ['total' => 10, 'reference' => 'R-1'], 'reviewer_id' => $reviewer, 'notes' => 'Checked'])
            ->assertCreated()->assertJsonPath('reviewer.id', $reviewer)->assertJsonPath('reviewable.id', $subject)->assertJsonPath('revision', 1)->assertJsonPath('data.total', 20)->assertJsonPath('data.reference', 'R-1')->json('id');
        $this->postJson($path, ['expected_revision' => 0, 'changes' => ['total' => 30]])->assertConflict()->assertJsonPath('current_revision', 1);
        $this->postJson($path, ['expected_revision' => 1, 'changes' => ['reference' => 'R-2']])->assertCreated()->assertJsonPath('data.total', 20)->assertJsonPath('revision', 2);
        $this->patchJson('/testing/revisions/'.$id, [])->assertConflict();
        $this->deleteJson('/testing/revisions/'.$id)->assertConflict();
        $this->assertDatabaseHas('review_revisions', ['id' => $id, 'revision' => 1, 'notes' => 'Checked']);
        $this->getJson($path)->assertOk()->assertJsonPath('latest.revision', 2)->assertJsonPath('count', 2);
    }

    public function test_hosts_can_configure_review_and_revision_models(): void
    {
        config(['reviews.models.review' => CustomReview::class, 'reviews.models.revision' => CustomRevision::class]);
        $subject = $this->subject();
        $this->postJson('/testing/subjects/'.$subject.'/reviews', [])->assertCreated()->assertJsonPath('custom_model', true);
        $this->getJson('/testing/subjects/'.$subject.'/reviews')->assertOk()->assertJsonPath('latest.custom_model', true);
        $this->postJson('/testing/subjects/'.$subject.'/revisions', ['expected_revision' => 0, 'changes' => ['field' => 'value']])->assertCreated()->assertJsonPath('custom_model', true);
        $this->getJson('/testing/subjects/'.$subject.'/revisions')->assertOk()->assertJsonPath('latest.custom_model', true);
    }

    public function test_review_events_wait_for_commit_and_do_not_escape_rollback(): void
    {
        Event::fake([ReviewCreated::class, ReviewRevisionCreated::class]);
        $subject = $this->subject();
        DB::beginTransaction();
        $this->postJson('/testing/subjects/'.$subject.'/reviews', [])->assertCreated();
        $this->postJson('/testing/subjects/'.$subject.'/revisions', ['expected_revision' => 0, 'changes' => ['field' => 'value']])->assertCreated();
        Event::assertNotDispatched(ReviewCreated::class);
        Event::assertNotDispatched(ReviewRevisionCreated::class);
        DB::rollBack();
        $this->assertDatabaseCount('reviews', 0);
        $this->assertDatabaseCount('review_revisions', 0);
        Event::assertNotDispatched(ReviewCreated::class);
        Event::assertNotDispatched(ReviewRevisionCreated::class);
        DB::beginTransaction();
        $this->postJson('/testing/subjects/'.$subject.'/reviews', [])->assertCreated();
        $this->postJson('/testing/subjects/'.$subject.'/revisions', ['expected_revision' => 0, 'changes' => ['field' => 'value']])->assertCreated();
        Event::assertNotDispatched(ReviewCreated::class);
        DB::commit();
        Event::assertDispatched(ReviewCreated::class);
        Event::assertDispatched(ReviewRevisionCreated::class);
    }

    public function test_identifier_upgrade_preserves_numeric_reviews(): void
    {
        $product = $this->postJson('/testing/products', ['title' => 'Legacy product'])->assertCreated()->json('id');
        $id = $this->postJson('/testing/products/'.$product.'/reviews', ['status' => 'accepted'])->assertCreated()->json('id');
        $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewable_id')->change();
            $table->unsignedBigInteger('reviewer_id')->nullable()->change();
        });
        $this->artisan('migrate')->assertSuccessful();
        $this->getJson('/testing/products/'.$product.'/reviews')->assertOk()->assertJsonPath('latest.id', $id)->assertJsonPath('latest.status', 'accepted');
        $subject = $this->subject();
        $this->postJson('/testing/subjects/'.$subject.'/reviews', [])->assertCreated()->assertJsonPath('reviewable.id', $subject);
    }

    public function test_hosts_can_configure_table_names(): void
    {
        config(['reviews.table' => 'custom_reviews', 'reviews.revisions_table' => 'custom_history']);
        $this->artisan('migrate:fresh')->assertSuccessful();
        $subject = $this->subject();
        $this->postJson('/testing/subjects/'.$subject.'/reviews', [])->assertCreated();
        $this->postJson('/testing/subjects/'.$subject.'/revisions', ['expected_revision' => 0, 'changes' => ['field' => 'value']])->assertCreated();
        $this->assertDatabaseCount('custom_reviews', 1);
        $this->assertDatabaseCount('custom_history', 1);
        $this->getJson('/testing/subjects/'.$subject.'/revisions')->assertOk()->assertJsonPath('count', 1);
    }

    public function test_invalid_revision_models_fail_with_a_configuration_error(): void
    {
        $subject = $this->subject();
        config(['reviews.models.revision' => \stdClass::class]);
        $this->withoutExceptionHandling();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('[reviews.models.revision] must extend');
        $this->postJson('/testing/subjects/'.$subject.'/revisions', ['expected_revision' => 0, 'changes' => ['field' => 'value']]);
    }
}

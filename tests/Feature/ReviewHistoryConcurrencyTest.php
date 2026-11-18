<?php

namespace Whilesmart\Reviews\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Whilesmart\Reviews\Tests\Support\HistoryTestCase;

class ReviewHistoryConcurrencyTest extends HistoryTestCase
{
    public function test_concurrent_nested_transactions_return_a_revision_conflict(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Concurrent revisions require MySQL and pcntl.');
        }
        $subject = $this->subject();
        $path = '/testing/subjects/'.$subject.'/revisions';
        $directory = sys_get_temp_dir().'/review-race-'.bin2hex(random_bytes(8));
        mkdir($directory);
        DB::purge();
        $children = [];
        try {
            foreach ([0, 1] as $index) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    throw new \RuntimeException('Cannot fork a request process.');
                }
                if ($pid === 0) {
                    pcntl_alarm(20);
                    try {
                        $waiting = false;
                        DB::listen(function ($event) use (&$waiting, $directory, $index) {
                            if ($waiting || $event->connection->transactionLevel() === 0 || ! str_contains($event->sql, 'revision_subjects') || str_contains(strtolower($event->sql), 'for update')) {
                                return;
                            }
                            $waiting = true;
                            touch($directory.'/ready-'.$index);
                            $deadline = microtime(true) + 10;
                            while (! is_file($directory.'/ready-'.(1 - $index))) {
                                if (microtime(true) > $deadline) {
                                    throw new \RuntimeException('The other request did not reach the transaction.');
                                }
                                usleep(1000);
                            }
                        });
                        $response = $this->postJson($path, ['expected_revision' => 0, 'changes' => ['total' => 20 + $index], 'initial_data' => ['reference' => 'R-1']]);
                        file_put_contents($directory.'/result-'.$index, json_encode(['status' => $response->getStatusCode()], JSON_THROW_ON_ERROR));
                        DB::disconnect();
                        exit(0);
                    } catch (\Throwable $exception) {
                        file_put_contents($directory.'/result-'.$index, $exception::class.': '.$exception->getMessage());
                        exit(1);
                    }
                }
                $children[] = $pid;
            }
            foreach ($children as $pid) {
                pcntl_waitpid($pid, $status);
                $this->assertTrue(pcntl_wifexited($status));
                $this->assertSame(0, pcntl_wexitstatus($status), implode('\n', array_map('file_get_contents', glob($directory.'/result-*'))));
            }
            $statuses = array_map(fn ($index) => json_decode(file_get_contents($directory.'/result-'.$index), true, flags: JSON_THROW_ON_ERROR)['status'], [0, 1]);
            sort($statuses);
            $this->assertSame([201, 409], $statuses);
            $this->assertDatabaseCount('review_revisions', 1);
            $winner = $this->getJson($path)->assertOk()->assertJsonPath('latest.revision', 1)->json('latest.data.total');
            $this->postJson($path, ['expected_revision' => 1, 'changes' => ['reference' => 'R-2']])->assertCreated()->assertJsonPath('data.total', $winner);
            $this->assertDatabaseCount('review_revisions', 2);
        } finally {
            foreach ($children as $pid) {
                pcntl_waitpid($pid, $status);
            }
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}

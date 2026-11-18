<?php

namespace Whilesmart\Reviews\Tests\Support;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Whilesmart\Reviews\Tests\Fixtures\HistoryServiceProvider;
use Whilesmart\Reviews\Tests\TestCase;

abstract class HistoryTestCase extends TestCase
{
    use DatabaseMigrations;

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), HistoryServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('reviews.string_identifiers', true);
        $app['config']->set('cache.default', 'array');
    }

    protected function subject(): string
    {
        return $this->postJson('/testing/subjects', ['title' => 'Evidence'])->assertCreated()->json('id');
    }
}

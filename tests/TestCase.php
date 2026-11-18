<?php

namespace Whilesmart\Reviews\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Whilesmart\Reviews\ReviewsServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench; // This automatically runs migrations in workbench/database

    protected function getPackageProviders($app)
    {
        return [
            ReviewsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        if (env('DB_CONNECTION') === 'mysql') {
            if (! str_ends_with((string) env('DB_DATABASE'), '_tests')) {
                throw new \RuntimeException('MySQL tests require a dedicated database ending in _tests.');
            }
            $app['config']->set('database.default', 'mysql');
        }
    }
}

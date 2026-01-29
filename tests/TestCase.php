<?php

namespace Whilesmart\Reviews\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Whilesmart\Reviews\ReviewsServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench; // This automatically runs migrations in workbench/database

    protected function getPackageProviders($app)
    {
        return [
            \Whilesmart\Reviews\ReviewsServiceProvider::class,
        ];
    }
}
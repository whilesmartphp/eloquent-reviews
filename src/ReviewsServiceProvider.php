<?php

namespace Whilesmart\Reviews;

use Illuminate\Support\ServiceProvider;

class ReviewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

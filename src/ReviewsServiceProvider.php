<?php

namespace Whilesmart\Reviews;

use Illuminate\Support\ServiceProvider;

class ReviewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/reviews.php', 'reviews');
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/reviews.php' => config_path('reviews.php')], 'reviews-config');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

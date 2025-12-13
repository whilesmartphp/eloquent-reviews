<?php

namespace Whilesmart\Reviews\Tests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Foundation\Application;


abstract class TestCase extends Orchestra
{

    // public function createApplication()
    // {
    //     $app = parent::createApplication(); // Calls the parent method to get the base application instance

    //     // 1. DEFINE MOCK USER MODEL (For the Reviewer relationship)
    //     $mockUser = new class extends Model {
    //         protected $table = 'users';
    //     };

    //     // 2. DEFINE MOCK REVIEWABLE MODEL (For the Polymorphic relationship)
    //     $mockReviewable = new class extends Model {
    //         protected $table = 'reviewable_dummies';
    //     };

    //     // 3. BIND THE MOCK CLASSES TO THE EXPECTED FQCNs in the IoC Container
    //     $app->instance('App\Models\User', $mockUser);
    //     $app->instance('App\Models\ReviewableDummy', $mockReviewable);

    //     // 4. Define the Morph Map
    //     Relation::enforceMorphMap([
    //         'reviewable_dummy' => 'App\Models\ReviewableDummy',
    //     ]);
        
    //     return $app;
    // }

    protected function setUp(): void
    {
        parent::setUp();
        

        Relation::enforceMorphMap([
            'reviewable_dummy' => 'App\Models\ReviewableDummy',
        ]);
    }

    /**
     * Define environment setup (e.g., database configuration).
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to SQLite in memory for fast testing
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Mock the FQCN of the User model used in the Reviewer relationship
        $app['config']->set('reviews.user_model', 'App\Models\User');
    }

    /**
     * Define database migrations and schemas required by the Review model.
     */
    protected function defineDatabaseMigrations(): void
    {
        // 1. Load the package migration for 'reviews' table
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // 2. Create the dummy 'users' table (required for reviewer_id foreign key constraint)
        Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        // 3. Create the dummy 'reviewable_dummies' table (required for reviewable polymorphic relation)
        Schema::create('reviewable_dummies', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            \Whilesmart\Reviews\ReviewsServiceProvider::class,
        ];
    }
}
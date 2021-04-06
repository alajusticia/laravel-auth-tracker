<?php

namespace ALajusticia\AuthTracker\Tests;

use ALajusticia\AuthTracker\AuthTrackerServiceProvider;
use ALajusticia\Expirable\ExpirableServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\SanctumServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->artisan('migrate')->run();

        $this->artisan('passport:install')->run();

        $this->setRoutes();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AuthTrackerServiceProvider::class,
            ExpirableServiceProvider::class,
            PassportServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Configuration for Laravel Passport
        $app['config']->set('auth.guards.api', [
            'driver' => 'passport',
            'provider' => 'passport_users',
        ]);

        $app['config']->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent-tracked',
                'model' => User::class,
            ],

            'passport_users' => [
                'driver' => 'eloquent-tracked',
                'model' => PassportUser::class,
            ],
        ]);
    }

    protected function setRoutes()
    {
        Passport::routes();

        Route::prefix('api')->middleware(['api', 'auth:api'])->group(function () {

            Route::get('/check', function (Request $request) {
                return response()->json($request->user()->currentLogin());
            });

            Route::post('/logout/others', function (Request $request) {
                return response()->json($request->user()->logoutOthers());
            });

            Route::post('/logout/all', function (Request $request) {
                return response()->json($request->user()->logoutAll());
            });

            Route::post('/logout/{id?}', function (Request $request, $id = null) {
                return response()->json($request->user()->logout($id));
            });
        });
    }
}

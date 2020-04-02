<?php

namespace ALajusticia\AuthTracker;

use ALajusticia\AuthTracker\Factories\IpProviderFactory;
use ALajusticia\AuthTracker\Macros\RouteMacros;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge default config
        $this->mergeConfigFrom(
            __DIR__.'/../config/auth_tracker.php', 'auth_tracker'
        );

        // Register commands
        $this->commands([
            Commands\InstallCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/auth_tracker.php' => config_path('auth_tracker.php'),
        ], 'config');

        // Publish controllers
        $this->publishes([
            __DIR__.'/Controllers/AuthTrackingController.stub' => app_path('Http/Controllers/Auth/AuthTrackingController.php'),
        ], 'controllers');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views/auth/list.blade.php' => base_path('resources/views/auth/list.blade.php'),
        ], 'views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register extended Eloquent user provider
        Auth::provider('eloquent-tracked', function ($app, array $config) {
            return new EloquentUserProviderExtended($app['hash'], $config['model']);
        });

        // Register event subscribers
        Event::subscribe('ALajusticia\AuthTracker\Listeners\AuthEventSubscriber');
        Event::subscribe('ALajusticia\AuthTracker\Listeners\PassportEventSubscriber');
        Event::subscribe('ALajusticia\AuthTracker\Listeners\SanctumEventSubscriber');

        // Register route macros
        Route::mixin(new RouteMacros);

        // Register Blade directives
        Blade::if('tracked', function () {
            return method_exists(request()->user(), 'logins');
        });
        Blade::if('ipLookup', function () {
            return IpProviderFactory::ipLookupEnabled();
        });
    }
}

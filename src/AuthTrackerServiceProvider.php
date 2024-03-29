<?php

namespace ALajusticia\AuthTracker;

use ALajusticia\AuthTracker\Factories\IpProviderFactory;
use ALajusticia\AuthTracker\Macros\RouteMacros;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
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

        // Register Blade directives
        Blade::if('tracked', function () {
            return method_exists(Request::user(), 'logins');
        });
        Blade::if('ipLookup', function () {
            return IpProviderFactory::ipLookupEnabled();
        });
    }
}

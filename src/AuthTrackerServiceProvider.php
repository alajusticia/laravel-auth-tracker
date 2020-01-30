<?php

namespace AnthonyLajusticia\AuthTracker;

use AnthonyLajusticia\AuthTracker\Factories\IpProviderFactory;
use AnthonyLajusticia\AuthTracker\Macros\RouteMacros;
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

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        // Publish controllers
        $this->publishes([
            __DIR__.'/Controllers/LoginController.php.stub' => app_path('Http/Controllers/Auth/LoginController.php'),
            __DIR__.'/Controllers/LogoutController.php.stub' => app_path('Http/Controllers/Auth/LogoutController.php'),
        ], 'web-controllers');
        $this->publishes([
            __DIR__.'/Controllers/Api/LoginController.php.stub' => app_path('Http/Controllers/Auth/Api/LoginController.php'),
            __DIR__.'/Controllers/Api/LogoutController.php.stub' => app_path('Http/Controllers/Auth/Api/LogoutController.php'),
        ], 'api-controllers');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views/auth/list.blade.php' => base_path('resources/views/auth/list.blade.php'),
        ], 'views');

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang/en/auth_tracker.php' => base_path('resources/lang/en/auth_tracker.php'),
        ], 'translations');

        // Register extended Eloquent user provider
        Auth::provider('eloquent-extended', function ($app, array $config) {
            return new EloquentUserProviderExtended($app['hash'], $config['model']);
        });

        // Register event subscribers
        Event::subscribe('AnthonyLajusticia\AuthTracker\Listeners\AuthEventSubscriber');
        Event::subscribe('AnthonyLajusticia\AuthTracker\Listeners\ApiAuthEventSubscriber');

        // Register route macros
        Route::mixin(new RouteMacros);

        // Register Blade directives
        Blade::if('ipLookup', function () {
            return IpProviderFactory::ipLookupEnabled();
        });
    }
}

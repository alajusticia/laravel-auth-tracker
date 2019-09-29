<?php

namespace AnthonyLajusticia\AuthTracker\Macros;

use Illuminate\Support\Facades\Route;

class RouteMacros
{
    /**
     * Get the routes for the web middleware.
     *
     * @return \Closure
     */
    public function authTracker()
    {
        return function ($prefix) {
            Route::namespace('Auth')->prefix($prefix)->group(function () {

                // Route to manage logins
                Route::get('/', 'LoginController@listLogins')->name('auth_tracker.list');

                // Logout routes
                Route::post('logout/{id}', 'LogoutController@logout')->name('auth_tracker.logout');
                Route::post('logout-all', 'LogoutController@logoutAll')->name('auth_tracker.logout.all');
                Route::post('logout-others', 'LogoutController@logoutOthers')->name('auth_tracker.logout.others');
            });
        };
    }

    /**
     * Get the routes for the api middleware.
     *
     * @return \Closure
     */
    public function apiAuthTracker()
    {
        return function ($prefix) {
            Route::namespace('Auth\Api')->prefix($prefix)->group(function () {

                // Routes to manage logins
                Route::get('/', 'LoginController@listLogins')->name('auth_tracker.api.list');

                // Logout routes
                Route::get('logout/{id?}', 'LogoutController@logout')->name('auth_tracker.api.logout');
                Route::get('logout-all', 'LogoutController@logoutAll')->name('auth_tracker.api.logout.all');
                Route::get('logout-others', 'LogoutController@logoutOthers')->name('auth_tracker.api.logout.others');
            });
        };
    }
}

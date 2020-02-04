<?php

namespace ALajusticia\AuthTracker\Macros;

use Illuminate\Support\Facades\Route;

class RouteMacros
{
    /**
     * Get the routes for the Laravel Auth Tracker.
     *
     * @return \Closure
     */
    public function authTracker()
    {
        return function ($path) {

            // Route to manage logins
            Route::get($path, 'Auth\AuthTrackingController@listLogins')->name('login.list');

            // Logout routes
            Route::middleware('auth')->group(function () {
                Route::post('logout/all', 'Auth\LoginController@logoutAll')->name('logout.all');
                Route::post('logout/others', 'Auth\LoginController@logoutOthers')->name('logout.others');
                Route::post('logout/{id}', 'Auth\LoginController@logoutById')->where('id', '[0-9]+')->name('logout.id');
            });
        };
    }
}

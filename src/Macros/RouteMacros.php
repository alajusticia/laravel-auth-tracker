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
        return function ($prefix) {

            Route::prefix($prefix)->group(function () {

                // Route to manage logins
                Route::get('/', 'Auth\AuthTrackingController@listLogins')->name('login.list');

                // Logout routes
                Route::middleware('auth')->group(function () {
                    Route::post('logout/all', 'Auth\AuthTrackingController@logoutAll')->name('logout.all');
                    Route::post('logout/others', 'Auth\AuthTrackingController@logoutOthers')->name('logout.others');
                    Route::post('logout/{id}', 'Auth\AuthTrackingController@logoutById')->where('id', '[0-9]+')->name('logout.id');
                });
            });
        };
    }
}

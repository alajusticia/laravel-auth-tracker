# Laravel Auth Tracker

Track and manage the sessions and Passport tokens in Laravel.

This package allows you to track separately each login (session or token), attaching informations by parsing the
User-Agent and saving the IP address.

Using a supported provider or creating your own custom providers, you can collect even more informations with
an IP address lookup to get, for example, the geolocation.

You can revoke every single login or all at once. In case of sessions with remember tokens, every session has its
own remember token. This way, you can logout a session without affecting the others. It solves this
[issue](https://github.com/laravel/ideas/issues/971).

* [Compatibility](#compatibility)
* [Installation](#installation)
  * [Create the logins table](#create-the-logins-table)
  * [Generate the scaffolding](#generate-the-scaffolding)
  * [Prepare your authenticatable models](#prepare-your-authenticatable-models)
  * [Prepare your LoginController](#prepare-your-logincontroller)
  * [Add the routes](#add-the-routes)
  * [Choose and install a user-agent parser](#choose-and-install-a-user-agent-parser)
  * [Configure the user provider](#configure-the-user-provider)
* [Configuration](#configuration)
* [Usage](#usage)
  * [Retrieving the logins](#retrieving-the-logins)
    * [Get all the logins](#get-all-the-logins)
    * [Get the current login](#get-the-current-login)
  * [Check for the current login](#check-for-the-current-login)
  * [Revoking logins](#revoking-logins)
    * [Revoke a specific login](#revoke-a-specific-login)
    * [Revoke all the logins](#revoke-all-the-logins)
    * [Revoke all the logins except the current one](#revoke-all-the-logins-except-the-current-one)
* [Routes](#routes)
  * [Web routes](#web-routes)
  * [API routes](#api-routes)
* [Notifications](#notifications)
* [The RequestContext object](#the-requestcontext-object)
* [IP address lookup](#ip-address-lookup)
  * [Custom provider](#custom-provider)
  * [Blade directive](#blade-directive)
  * [Handle API errors](#handle-api-errors)

## Compatibility

- This package has been tested with Laravel >= 5.8.

- It works with all the session drivers supported by Laravel, except of course the cookie driver which saves
the sessions only in the client browser and the array driver.

- To track API tokens, it supports the official Laravel Passport package.

- In case you want to use Passport with multiple user providers, this package works with the
`sfelix-martins/passport-multiauth` package (see [here](https://github.com/sfelix-martins/passport-multiauth)).

## Installation

Install the package with composer:

```bash
composer require anthonylajusticia/laravel-auth-tracker
```

The service provider will automatically get registered. Or you may manually add it in your `config/app.php` file:

```php
'providers' => [
    // ...
    AnthonyLajusticia\AuthTracker\AuthTrackerServiceProvider::class,
];
```

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="AnthonyLajusticia\AuthTracker\AuthTrackerServiceProvider" --tag="config"
```

### Create the logins table

Before running the migrations, you can change the name of the table that will be used to save the logins
(named by default `logins`) with the `table_name` option of the configuration file.

Launch the database migrations to create the required table:

```bash
php artisan migrate
```

### Generate the scaffolding

This package provides a command to help you getting started by generating the scaffolding of the Auth Tracker.

The command will publish all the controllers, views, notifications and translations in your app.

Just run the `auth-tracker:install` command and answer the prompt depending on what you need to install the tracker for
(session tracking, Passport tokens tracking, or both):

```bash
php artisan auth-tracker:install
```

### Prepare your authenticatable models

In order to track the logins of your app's users, add the `AnthonyLajusticia\AuthTracker\Traits\AuthTracking` trait
on each of your authenticatable models that you want to track:

```php
use AnthonyLajusticia\AuthTracker\Traits\AuthTracking;
use Illuminate\Foundation\Auth\User as Authenticatable;
// ...

class User extends Authenticatable
{
    use AuthTracking;

    // ...
}
```

### Prepare your LoginController

Replace the `Illuminate\Foundation\Auth\AuthenticatesUsers` trait of your `App\Http\Controllers\Auth\LoginController`
by the `AnthonyLajusticia\AuthTracker\Traits\AuthenticatesWithTracking` trait provided by this package.

This trait adds a `listLogins` method that you can override to suit your needs.

Also, it overrides the `sendLoginResponse` method by removing the session regeneration.
But don't worry, there's no security issue here.
Instead, this package do the session regeneration in an event
listener on the login event (before saving the informations of the new login).
Because of the `sendLoginResponse` regenerating the session ID after the login event has been dispatched,
this approach allows to get the right session ID generated by a new login.

Make sur that the `listLogins` method use the auth middleware.

Here an example of what the constructor of your `LoginController` might look like:

```php
public function __construct()
{
    $this->middleware('guest')->except(['logout', 'listLogins']);
    $this->middleware('auth')->only(['logout', 'listLogins']);
}
```

### Add the routes

This package comes with several routes to manage the logins.

Add those routes using the route macros provided.
You can choose the URL prefix under which those routes will be accessible.

- If you use the Auth Tracker to track the sessions, add this in your web routes:

```php
Route::authTracker('security'); // Add the required routes under the prefix "security"
```

- If you use the Auth Tracker to track the Passport tokens, add this in your API routes:

```php
Route::apiAuthTracker('security'); // Add the required routes under the prefix "security"
```

You may add the route for managing sessions in your views:
`{{ route('auth_tracker.list') }}`

### Choose and install a user-agent parser

This package relies on a User-Agent parser to extract the informations.

Currently, it supports two of the most popular parsers:
- WhichBrowser ([https://github.com/WhichBrowser/Parser-PHP](https://github.com/WhichBrowser/Parser-PHP))
- Agent ([https://github.com/jenssegers/agent](https://github.com/jenssegers/agent))

Before using the Auth Tracker, you need to choose a supported parser, install it and indicate in the configuration file which one you want
to use.

### Configure the user provider

This package comes with a modified Eloquent user provider that retrieve remembered users from the logins table instead of the users table.

In your `config/auth.php` configuration file, use the `eloquent-extended` driver in the user providers list for the users you want to track:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent-extended',
        'model' => App\User::class,
    ],
    
    // ...
],
```

## Configuration

When publishing the configuration file, you get `config/auth_tracker.php`:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Use this option to customize the name of the table used to save the
    | logins in the database.
    |
    */

    'table_name' => 'logins',

    /*
    |--------------------------------------------------------------------------
    | Remember Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you can specify the lifetime of the remember tokens.
    |
    | Must be an integer representing the number of days.
    |
    */

    'remember_lifetime' => 365, // 1 year

    /*
    |--------------------------------------------------------------------------
    | Parser
    |--------------------------------------------------------------------------
    |
    | Choose which parser to use to parse the User-Agent.
    | You will need to install the package of the corresponding parser.
    |
    | Supported values:
    | 'agent' (see https://github.com/jenssegers/agent)
    | 'whichbrowser' (see https://github.com/WhichBrowser/Parser-PHP)
    |
    */

    'parser' => 'whichbrowser',

    /*
    |--------------------------------------------------------------------------
    | Notify
    |--------------------------------------------------------------------------
    |
    | Enable to send a notification to the user, each time a login is made
    | on his account.
    | It gives users the possibility to be notified immediately and to be able
    | to check the login informations and detect any unauthorized login.
    |
    | It will look for a "app/Notifications/LoggedIn.php" notification file,
    | you can get an example by installing the provided scaffolding (with the
    | "php artisan auth-tracker:install" command).
    |
    | boolean
    |
    */

    'notify' => true,

    /*
    |--------------------------------------------------------------------------
    | IP Address Lookup
    |--------------------------------------------------------------------------
    |
    | This package provides a feature to get additional data about the client
    | IP (like the geolocation) by calling an external API.
    |
    | This feature makes usage of the Guzzle PHP HTTP client to make the API
    | calls, so you will have to install the corresponding package
    | (see https://github.com/guzzle/guzzle) if you are considering to enable
    | the IP address lookup.
    |
    */

    'ip_lookup' => [

        /*
        |--------------------------------------------------------------------------
        | Provider
        |--------------------------------------------------------------------------
        |
        | If you want to enable the IP address lookup, choose a supported
        | IP address lookup provider.
        |
        | Supported values:
        | - 'ip-api' (see https://members.ip-api.com/)
        | - false (to disable the IP address lookup feature)
        | - any other custom name declared as a key of the custom_providers array
        |
        */

        'provider' => false,

        /*
        |--------------------------------------------------------------------------
        | Timeout
        |--------------------------------------------------------------------------
        |
        | Float describing the number of seconds to wait while trying to connect
        | to the provider's API.
        |
        | If the request takes more time, the IP address lookup will be ignored
        | and the AnthonyLajusticia\AuthTracker\Events\FailedApiCall will be
        | dispatched, receiving the attribute $exception containing the
        | GuzzleHttp\Exception\TransferException.
        |
        | Use 0 to wait indefinitely.
        |
        */

        'timeout' => 1.0,

        /*
        |--------------------------------------------------------------------------
        | Environments
        |--------------------------------------------------------------------------
        |
        | Indicate here an array of environnments for which you want to enable
        | the IP address lookup.
        |
        */

        'environments' => [
            'production',
        ],

        /*
        |--------------------------------------------------------------------------
        | Custom Providers
        |--------------------------------------------------------------------------
        |
        | You can create your own custom providers for the IP address lookup feature.
        | See in the README file how to create an IP provider class and declare it
        | in the array below.
        |
        | Format: 'name_of_your_provider' => ProviderClassName::class
        |
        */

        'custom_providers' => [],
    ],
];
```

## Usage

The `AuthTracking` trait provided by this package surcharge your users models with methods to list their logins and to
give you full individual control on them.

### Retrieving the logins

#### Get all the logins

```php
$logins = request()->user()->logins;
```

#### Get the current login

```php
$login = request()->user()->currentLogin;
```

### Check for the current login

Each login instance comes with a dynamic `is_current` attribute.
It's a boolean that indicates if the login instance is the current login.

### Revoking logins

#### Revoke a specific login

To revoke a specific login, use the `logout` method with the ID of the login you want to revoke.
If no parameter is given, the current login will be revoked.

```php
request()->user()->logout(1); // Revoke the login where id=1
```

```php
request()->user()->logout(); // Revoke the current login
```

#### Revoke all the logins

We can destroy all the sessions and revoke all the Passport tokens by using the `logoutAll` method.
Useful when, for example, the user's password is modified and we want to logout all the devices.

This feature destroys all sessions, even those remembered.

```php
request()->user()->logoutAll();
```

#### Revoke all the logins except the current one

The `logoutOthers` method acts in the same way as the `logoutAll` method except that it keeps the current
session / Passport token alive.

```php
request()->user()->logoutOthers();
```

## Routes

Here are the routes added by this package (via the [route macros](#add-the-routes)):

### Web routes

```php
Route::namespace('Auth')->prefix($prefix)->group(function () {

    // Route to manage logins
    Route::get('/', 'LoginController@listLogins')->name('auth_tracker.list');

    // Logout routes
    Route::post('logout/{id}', 'LogoutController@logout')->name('auth_tracker.logout');
    Route::post('logout-all', 'LogoutController@logoutAll')->name('auth_tracker.logout.all');
    Route::post('logout-others', 'LogoutController@logoutOthers')->name('auth_tracker.logout.others');
});
```

### API routes

```php
Route::namespace('Auth\Api')->prefix($prefix)->group(function () {

    // Routes to manage logins
    Route::get('/', 'LoginController@listLogins')->name('auth_tracker.api.list');

    // Logout routes
    Route::get('logout/{id?}', 'LogoutController@logout')->name('auth_tracker.api.logout');
    Route::get('logout-all', 'LogoutController@logoutAll')->name('auth_tracker.api.logout.all');
    Route::get('logout-others', 'LogoutController@logoutOthers')->name('auth_tracker.api.logout.others');
});
```

## Notifications

If you set the option `notify` to `true` in the configuration file, the package will look for a
`App\Notifications\LoggedIn` file and send a notification to the user on each new login with
the device and IP address informations.

Executing the `php artisan auth-tracker:install` command, you get an example of what this notification might look like.

## The RequestContext object

In your notification, you can rely on a `RequestContext` object containing all the informations collected on the request.

Attributes available:
```php
$this->context->userAgent; // The full, unparsed, User-Agent header
$this->context->ip; // The IP address
```

Methods available:
```php
$this->context->parser(); // Returns the parser used to parse the User-Agent header
$this->context->ip(); // Returns the IP address lookup provider
```

Methods available in the parser:
```php
$this->context->getDevice(); // The name of the device (MacBook...)
$this->context->getDeviceType(); // The type of the device (desktop, mobile, tablet, phone...)
$this->context->getPlatform(); // The name of the platform (macOS...)
$this->context->getBrowser(); // The name of the browser (Chrome...)
```

Methods available in the IP address lookup provider:
```php
$this->context->getCountry(); // The name of the country
$this->context->getRegion(); // The name of the region
$this->context->getCity(); // The name of the city
$this->context->getResult(); // The entire result of the API call as a Laravel collection

// And all your custom methods in the case of a custom provider
```

## IP address lookup

By default, the Auth Tracker collects the IP address and the informations given by the User-Agent header.

But you can go even further and collect other informations about the IP address, like the geolocation.

To do so, you first have to enable the IP lookup feature in the configuration file.

For now, this package comes with one officially supported provider for IP address lookup.

### Custom provider

You can add your own providers by creating a class that implements the
`AnthonyLajusticia\AuthTracker\Interfaces\IpProvider` interface and use the
`AnthonyLajusticia\AuthTracker\Traits\MakesApiCalls` trait.

Your custom class have to be registered in the `custom_providers` array of the configuration file.

Let's see an example of an IP lookup provider with the built-in `IpApi` provider:

```php
use AnthonyLajusticia\AuthTracker\Interfaces\IpProvider;
use AnthonyLajusticia\AuthTracker\Traits\MakesApiCalls;
use GuzzleHttp\Psr7\Request;

class IpApi implements IpProvider
{
    use MakesApiCalls;

    /**
     * Get the Guzzle request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return new Request('GET', 'http://ip-api.com/json/'.request()->ip().'?fields=25');
    }

    /**
     * Get the country name.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->result->get('country');
    }

    /**
     * Get the region name.
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->result->get('regionName');
    }

    /**
     * Get the city name.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->result->get('city');
    }
}
```

As you can see, the class have a `getRequest` method that must return a `GuzzleHttp\Psr7\Request` instance.

Guzzle utilizes PSR-7 as the HTTP message interface. Check its documentation:
[http://docs.guzzlephp.org/en/stable/psr7.html](http://docs.guzzlephp.org/en/stable/psr7.html)

The `IpProvider` interface comes with required methods related to the geolocation.
All keys of the API response are accessible in your provider via `$this->result`, which is a Laravel collection.

If you want to collect other informations, you can add a `getCustomData` method in your custom provider.
This custom data will be saved in the logins table in the `ip_data` JSON column.
Let's see an example of additional data:

```php
public function getCustomData()
{
    return [
        'country_code' => $this->result->get('countryCode'),
        'latitude' => $this->result->get('lat'),
        'longitude' => $this->result->get('lon'),
        'timezone' => $this->result->get('timezone'),
        'isp_name' => $this->result->get('isp'),
    ];
}
```

### Blade directive

This package adds a Blade directive to help you know in your templates if the IP lookup feature is enabled:

```php
@ipLookup
    {{ $login->location }}
@endipLookup
```

### Handle API errors

In case of an exception throwed during the API call of your IP address lookup provider, the FailedApiCall event
is fired by this package.

This event has an exception attribute containing the GuzzleHttp\Exception\TransferException
(see [Guzzle documentation](http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions)).

You can listen to this event to add your own logic.

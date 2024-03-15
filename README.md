# Laravel Auth Tracker

> ⚠️ This package is now archived. Use [Laravel Logins](https://github.com/alajusticia/laravel-logins) instead.

#### Track and manage sessions, Passport tokens and Sanctum tokens in Laravel.

This package allows you to track separately each login (session or token), attaching informations by parsing the
User-Agent and saving the IP address.

Using a supported provider or creating your own custom providers, you can collect even more informations with
an IP address lookup to get, for example, the geolocation.

You can revoke every single session/token or all at once.
In case of sessions with remember tokens, every session has its own remember token.
This way, you can revoke a session without affecting the others.

* [Compatibility](#compatibility)
* [Installation](#installation)
  * [Create the logins table](#create-the-logins-table)
  * [Prepare your authenticatable models](#prepare-your-authenticatable-models)
  * [Choose and install a user-agent parser](#choose-and-install-a-user-agent-parser)
  * [Configure the user provider (optional)](#configure-the-user-provider-optional)
  * [Laravel Sanctum](#laravel-sanctum)
* [Usage](#usage)
  * [Retrieving the logins](#retrieving-the-logins)
    * [Get all the logins](#get-all-the-logins)
    * [Get the current login](#get-the-current-login)
  * [Check for the current login](#check-for-the-current-login)
  * [Revoking logins](#revoking-logins)
    * [Revoke a specific login](#revoke-a-specific-login)
    * [Revoke all the logins](#revoke-all-the-logins)
    * [Revoke all the logins except the current one](#revoke-all-the-logins-except-the-current-one)
* [Events](#events)
  * [Login](#login)
* [IP address lookup](#ip-address-lookup)
  * [Ip2Location Lite DB3](#ip2location-lite-db3)
  * [Custom provider](#custom-provider)
  * [Handle API errors](#handle-api-errors)
* [Blade directives](#blade-directives)
* [License](#license)

## Compatibility

- For recent Laravel versions, use [Laravel Logins](https://github.com/alajusticia/laravel-logins).

- For previous versions of Laravel (v5.8, v6 and v7), use the [v2](https://github.com/alajusticia/laravel-auth-tracker/tree/v2).

- It works with all the session drivers supported by Laravel, except of course the cookie driver which saves
the sessions only in the client browser and the array driver.

- To track API tokens, it supports the official **Laravel Passport (>= 7.5)** and **Laravel Sanctum (v2)** packages.

## Installation

**/!\ This documentation is for the v3 (WIP). The documentation for the latest release (v2) is available here: [https://github.com/alajusticia/laravel-auth-tracker/tree/v2](https://github.com/alajusticia/laravel-auth-tracker/tree/v2)**

Install the package with composer:

```bash
composer require alajusticia/laravel-auth-tracker
```

Publish the configuration file (`config/auth_tracker.php`) with:

```bash
php artisan vendor:publish --provider="ALajusticia\AuthTracker\AuthTrackerServiceProvider" --tag="config"
```

### Create the logins table

Before running the migrations, you can change the name of the table that will be used to save the logins
(named by default `logins`) with the `table_name` option of the configuration file.

Launch the database migrations to create the required table:

```bash
php artisan migrate
```

### Prepare your authenticatable models

In order to track the logins of your app's users, add the `ALajusticia\AuthTracker\Traits\AuthTracking` trait
on each of your authenticatable models that you want to track:

```php
use ALajusticia\AuthTracker\Traits\AuthTracking;
use Illuminate\Foundation\Auth\User as Authenticatable;
// ...

class User extends Authenticatable
{
    use AuthTracking;

    // ...
}
```

### Choose and install a user-agent parser

This package relies on a User-Agent parser to extract the informations.

Currently, it supports two of the most popular parsers:
- WhichBrowser ([https://github.com/WhichBrowser/Parser-PHP](https://github.com/WhichBrowser/Parser-PHP))
- Agent ([https://github.com/jenssegers/agent](https://github.com/jenssegers/agent))

Before using the Auth Tracker, you need to choose a supported parser, install it and indicate in the configuration file which one you want
to use.

### Configure the user provider (optional)

This step is optional if your application has only stateless authentication.

If you want to use the Auth Tracker for stateful authentication, this package comes with a modified Eloquent user provider that retrieve 
remembered users from the logins table instead of the users table.

In your `config/auth.php` configuration file, use the `eloquent-tracked` driver in the user providers list for the users you want to track:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent-tracked',
        'model' => App\Models\User::class,
    ],
    
    // ...
],
```

### Laravel Sanctum

In the actual version (2.9) of the Laravel Sanctum package, there is no event allowing us to know when
an API token is created.

If you are issuing API tokens with Laravel Sanctum and want to enable auth tracking,
you will have to dispatch an event provided by the Auth Tracker.

Dispatch the `ALajusticia\AuthTracker\Events\PersonalAccessTokenCreated` event passing the personal access token
newly created by the `createToken` method of the Laravel Sanctum trait.

Based on the [example](https://laravel.com/docs/8.x/sanctum#issuing-mobile-api-tokens) provided by
the Laravel Sanctum documentation, it might look like this:

```php
use ALajusticia\AuthTracker\Events\PersonalAccessTokenCreated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $personalAccessToken = $user->createToken($request->device_name);
    
    event(new PersonalAccessTokenCreated($personalAccessToken)); // Dispatch here the event

    return $personalAccessToken->plainTextToken;
});
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
$login = request()->user()->currentLogin();
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

We can destroy all the sessions and revoke all the Passport/Sanctum tokens by using the `logoutAll` method.
Useful when, for example, the user's password is modified and we want to logout all the devices.

This feature destroys all sessions, even those remembered.

```php
request()->user()->logoutAll();
```

#### Revoke all the logins except the current one

The `logoutOthers` method acts in the same way as the `logoutAll` method except that it keeps the current
session or Passport/Sanctum token alive.

```php
request()->user()->logoutOthers();
```

## Events

### Login

On a new login, you can listen to the event `ALajusticia\AuthTracker\Events\Login`.
It receives a `RequestContext` object containing all the informations collected on the request, accessible on the event
with the `context` property.

Properties available:
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
$this->context->parser()->getDevice(); // The name of the device (MacBook...)
$this->context->parser()->getDeviceType(); // The type of the device (desktop, mobile, tablet, phone...)
$this->context->parser()->getPlatform(); // The name of the platform (macOS...)
$this->context->parser()->getBrowser(); // The name of the browser (Chrome...)
```

Methods available in the IP address lookup provider:
```php
$this->context->ip()->getCountry(); // The name of the country
$this->context->ip()->getRegion(); // The name of the region
$this->context->ip()->getCity(); // The name of the city
$this->context->ip()->getResult(); // The entire result of the API call as a Laravel collection

// And all your custom methods in the case of a custom provider
```

## IP address lookup

By default, the Auth Tracker collects the IP address and the informations given by the User-Agent header.

But you can go even further and collect other informations about the IP address, like the geolocation.

To do so, you first have to enable the IP lookup feature in the configuration file.

This package comes with two officially supported providers for IP address lookup
(see the IP Address Lookup section in the `config/auth_tracker.php` configuration file).

### Ip2Location Lite DB3

This package officially support the IP address geolocation with the Ip2Location Lite DB3.

Here are the steps to enable and use it:

- Download the current version of the database and import it in your database as explained in the documentation:
[https://lite.ip2location.com/database/ip-country-region-city](https://lite.ip2location.com/database/ip-country-region-city)

- Set the name of the `ip_lookup.provider` option to `ip2location-lite` in the `config/auth_tracker.php` configuration file

- Indicate the name of the tables used in your database for IPv4 and IPv6 in the `config/auth_tracker.php` configuration file
(by default it uses the same names as the documentation: `ip2location_db3` and `ip2location_db3_ipv6`)

### Custom provider

You can add your own providers by creating a class that implements the
`ALajusticia\AuthTracker\Interfaces\IpProvider` interface and use the
`ALajusticia\AuthTracker\Traits\MakesApiCalls` trait.

Your custom class have to be registered in the `custom_providers` array of the configuration file.

Let's see an example of an IP lookup provider with the built-in `IpApi` provider:

```php
use ALajusticia\AuthTracker\Interfaces\IpProvider;
use ALajusticia\AuthTracker\Traits\MakesApiCalls;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Request;

class IpApi implements IpProvider
{
    use MakesApiCalls;

    /**
     * Get the Guzzle request.
     *
     * @return GuzzleRequest
     */
    public function getRequest()
    {
        return new GuzzleRequest('GET', 'http://ip-api.com/json/' . Request::ip() . '?fields=25');
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

As you can see, the class has a `getRequest` method that must return a `GuzzleHttp\Psr7\Request` instance.

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

### Handle API errors

In case of an exception throwed during the API call of your IP address lookup provider, the FailedApiCall event
is fired by this package.

This event has an exception attribute containing the GuzzleHttp\Exception\TransferException
(see [Guzzle documentation](http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions)).

You can listen to this event to add your own logic.

## Blade directives

Check if the auth tracking is enabled for the current user:

```php
@tracked
    <a href="{{ route('login.list') }}">Security</a>
@endtracked
```

Check if the IP lookup feature is enabled:

```php
@ipLookup
    {{ $login->location }}
@endipLookup
```

## License

Open source, licensed under the [MIT license](LICENSE).

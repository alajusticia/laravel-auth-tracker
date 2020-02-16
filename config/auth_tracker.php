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
        | - 'ip2location-lite' (see https://lite.ip2location.com/database/ip-country-region-city)
        | - 'ip-api' (see https://members.ip-api.com)
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
        | and the ALajusticia\AuthTracker\Events\FailedApiCall will be
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

        /*
        |--------------------------------------------------------------------------
        | Ip2Location
        |--------------------------------------------------------------------------
        |
        | If you are using 'ip2location-lite' provider, here you may change the
        | name of the tables for IPv4 and IPv6.
        |
        */

        'ip2location' => [
            'ipv4_table' => 'ip2location_db3',
            'ipv6_table' => 'ip2location_db3_ipv6',
        ],
    ],
];

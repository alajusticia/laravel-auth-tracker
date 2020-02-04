<?php

namespace ALajusticia\AuthTracker\Factories;

use ALajusticia\AuthTracker\Exceptions\CustomIpProviderException;
use ALajusticia\AuthTracker\Exceptions\IpProviderException;
use ALajusticia\AuthTracker\Interfaces\IpProvider;
use ALajusticia\AuthTracker\IpProviders\IpApi;
use Illuminate\Support\Facades\App;

class IpProviderFactory
{
    /**
     * Build a new IP provider.
     *
     * @param string $name
     * @return IpApi|object|void
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public static function build($name)
    {
        if (self::ipLookupEnabled()) {
            $customProviders = config('auth_tracker.ip_lookup.custom_providers');

            if ($customProviders && array_key_exists($name, $customProviders)) {

                // Use of a custom IP address lookup provider

                if (!in_array(IpProvider::class, class_implements($customProviders[$name]))) {

                    // The custom IP provider class doesn't
                    // implement the required interface

                    throw new CustomIpProviderException;
                }

                return new $customProviders[$name];

            } else {

                // Use of an officially supported IP address lookup provider

                switch ($name) {
                    case 'ip-api':
                        return new IpApi;
                    default:
                        throw new IpProviderException;
                }
            }
        }
    }

    /**
     * Check if the IP lookup feature is enabled.
     *
     * @return bool
     */
    public static function ipLookupEnabled()
    {
        return config('auth_tracker.ip_lookup.provider') &&
            App::environment(config('auth_tracker.ip_lookup.environments'));
    }
}

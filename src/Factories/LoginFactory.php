<?php

namespace ALajusticia\AuthTracker\Factories;

use ALajusticia\AuthTracker\Events\PersonalAccessTokenCreated;
use ALajusticia\AuthTracker\Models\Login;
use ALajusticia\AuthTracker\RequestContext;
use Illuminate\Auth\Events\Login as LoginEvent;
use Laravel\Passport\Events\AccessTokenCreated;

class LoginFactory
{
    /**
     * Build a new Login.
     *
     * @param LoginEvent|AccessTokenCreated $event
     * @param RequestContext $context
     * @return Login
     */
    public static function build($event, RequestContext $context)
    {
        $login = new Login();

        // Common attributes ------------------------------------------------------------------

        $login->fill([
            'user_agent' => $context->userAgent,
            'ip' => $context->ip,
            'device_type' => $context->parser()->getDeviceType(),
            'device' => $context->parser()->getDevice(),
            'platform' => $context->parser()->getPlatform(),
            'browser' => $context->parser()->getBrowser(),
        ]);

        // If we have the IP geolocation data
        if ($context->ip()) {
            $login->fill([
                'city' => $context->ip()->getCity(),
                'region' => $context->ip()->getRegion(),
                'country' => $context->ip()->getCountry(),
            ]);

            // Custom additional data?
            if (method_exists($context->ip(), 'getCustomData') &&
                $context->ip()->getCustomData()) {

                $login->ip_data = $context->ip()->getCustomData();
            }
        }

        // Specific attributes ----------------------------------------------------------------

        if ($event instanceof AccessTokenCreated) {

            $login->oauth_access_token_id = $event->tokenId;

        } elseif ($event instanceof PersonalAccessTokenCreated) {

            $login->personal_access_token_id = $event->personalAccessToken->id;

        } else {

            $login->fill([
                'session_id' => session()->getId(),
                'remember_token' => $event->remember ? $event->user->getRememberToken() : null,
            ]);

        }

        // ------------------------------------------------------------------------------------

        return $login;
    }
}

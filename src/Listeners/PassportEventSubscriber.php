<?php

namespace ALajusticia\AuthTracker\Listeners;

use ALajusticia\AuthTracker\Factories\LoginFactory;
use ALajusticia\AuthTracker\RequestContext;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

class PassportEventSubscriber
{
    public function handleAccessTokenCreation(AccessTokenCreated $event)
    {
        // Get the created access token
        $accessToken = Token::find($event->tokenId);

        // Get the authenticated user
        $provider = config('auth.guards.api.provider');
        $userModel = config('auth.providers.'.$provider.'.model');
        $user = call_user_func([$userModel, 'find'], $accessToken->user_id);

        if ($this->tracked($user)) {

            // Get as much information as possible about the request
            $context = new RequestContext;

            // Build a new login
            $login = LoginFactory::build($event, $context);

            // Set the expiration date
            $login->expiresAt($accessToken->expires_at);

            // Attach the login to the user and save it
            $user->logins()->save($login);

            if (Request::input('grant_type') !== 'refresh_token') {

                event(new \ALajusticia\AuthTracker\Events\Login($user, $context));
            }
        }
    }

    /**
     * Tracking enabled for this user?
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return bool
     */
    protected function tracked($user)
    {
        return in_array('ALajusticia\AuthTracker\Traits\AuthTracking', class_uses($user));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Laravel\Passport\Events\AccessTokenCreated',
            'ALajusticia\AuthTracker\Listeners\PassportEventSubscriber@handleAccessTokenCreation'
        );
    }
}

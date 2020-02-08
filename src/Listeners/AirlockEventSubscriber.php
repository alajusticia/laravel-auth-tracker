<?php

namespace ALajusticia\AuthTracker\Listeners;

use ALajusticia\AuthTracker\Events\PersonalAccessTokenCreated;
use ALajusticia\AuthTracker\Factories\LoginFactory;
use ALajusticia\AuthTracker\RequestContext;
use Carbon\Carbon;

class AirlockEventSubscriber
{
    public function handlePersonalAccessTokenCreation(PersonalAccessTokenCreated $event)
    {
        // Get the authenticated user
        $user = $event->personalAccessToken->tokenable;

        if ($this->tracked($user)) {

            // Get as much information as possible about the request
            $context = new RequestContext;

            // Build a new login
            $login = LoginFactory::build($event, $context);

            // Set the expiration date
            $login->expiresAt(Carbon::now()->addMinutes(config('airlock.expiration')));

            // Attach the login to the user and save it
            $user->logins()->save($login);

            event(new \ALajusticia\AuthTracker\Events\Login($user, $context));
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
            'ALajusticia\AuthTracker\Events\PersonalAccessTokenCreated',
            'ALajusticia\AuthTracker\Listeners\AirlockEventSubscriber@handlePersonalAccessTokenCreation'
        );
    }
}

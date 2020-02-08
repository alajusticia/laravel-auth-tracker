<?php

namespace ALajusticia\AuthTracker\Listeners;

use ALajusticia\AuthTracker\Factories\LoginFactory;
use ALajusticia\AuthTracker\Models\Login;
use ALajusticia\AuthTracker\RequestContext;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Auth\Recaller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthEventSubscriber
{
    public function handleSuccessfulLogin(LoginEvent $event)
    {
        if ($this->tracked($event->user)) {

            if (Auth::viaRemember()) {
                // Logged in via remember token

                if (!is_null($recaller = $this->recaller())) {

                    // Update session id
                    Login::where('remember_token', $recaller->token())->update([
                        'session_id' => session()->getId()
                    ]);
                }
            } else {
                // Initial login

                // Regenerate the session ID to avoid session fixation attacks
                session()->regenerate();

                // Get as much information as possible about the request
                $context = new RequestContext;

                // Build a new login
                $login = LoginFactory::build($event, $context);

                // Set the expiration date based on whether it is a remembered login or not
                if ($event->remember) {
                    $login->expiresAt(Carbon::now()->addDays(config('auth_tracker.remember_lifetime', 365)));
                } else {
                    $login->expiresAt(Carbon::now()->addMinutes(config('session.lifetime')));
                }

                // Attach the login to the user and save it
                $event->user->logins()->save($login);

                // Update the remember token
                $this->updateRememberToken($event->user, Str::random(60));

                event(new \ALajusticia\AuthTracker\Events\Login($event->user, $context));
            }
        }
    }

    public function handleSuccessfulLogout($event)
    {
        if ($this->tracked($event->user)) {

            // Delete login
            $event->user->logins()->where('session_id', session()->getId())
                ->delete();
        }
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @return Recaller|null
     */
    protected function recaller()
    {
        if (is_null(request())) {
            return null;
        }

        if ($recaller = request()->cookies->get(Auth::guard()->getRecallerName())) {
            return new Recaller($recaller);
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    protected function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->timestamps = false;
        $user->save();
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
            'Illuminate\Auth\Events\Login',
            'ALajusticia\AuthTracker\Listeners\AuthEventSubscriber@handleSuccessfulLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'ALajusticia\AuthTracker\Listeners\AuthEventSubscriber@handleSuccessfulLogout'
        );
    }
}

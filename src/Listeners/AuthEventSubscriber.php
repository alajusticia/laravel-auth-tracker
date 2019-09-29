<?php

namespace AnthonyLajusticia\AuthTracker\Listeners;

use AnthonyLajusticia\AuthTracker\Factories\LoginFactory;
use AnthonyLajusticia\AuthTracker\Models\Login;
use AnthonyLajusticia\AuthTracker\RequestContext;
use App\Notifications\LoggedIn;
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
        if (Auth::viaRemember()) {
            // Logged in via remember token

            if (! is_null($recaller = $this->recaller())) {

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

            // Notify the user by email that a login has just been made
            if (config('auth_tracker.notify')) {
                $event->user->notify(new LoggedIn($context));
            }
        }
    }

    public function handleSuccessfulLogout($event)
    {
        // Delete login
        $event->user->logins()->where('session_id', session()->getId())
            ->delete();
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
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'AnthonyLajusticia\AuthTracker\Listeners\AuthEventSubscriber@handleSuccessfulLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'AnthonyLajusticia\AuthTracker\Listeners\AuthEventSubscriber@handleSuccessfulLogout'
        );
    }
}

<?php

namespace AnthonyLajusticia\AuthTracker\Traits;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

trait AuthenticatesWithTracking
{
    use AuthenticatesUsers;

    /**
     * List all active logins for the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function listLogins(Request $request)
    {
        return view('auth.list', ['logins' => $request->user()->logins->sortByDesc('is_current')]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }
}

<?php

namespace ALajusticia\AuthTracker\Traits;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

trait AuthenticatesWithTracking
{
    use AuthenticatesUsers;

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

    public function logoutById(Request $request, $id)
    {
        $request->user()->logout($id);

        return $this->redirectToLoginsList();
    }

    public function logoutAll(Request $request)
    {
        $request->user()->logoutAll();

        return $this->loggedOut($request) ?: redirect('/');
    }

    public function logoutOthers(Request $request)
    {
        $request->user()->logoutOthers();

        return $this->redirectToLoginsList();
    }

    protected function redirectToLoginsList()
    {
        return redirect()->route('auth_tracker.list')->with([
            'status' => [
                'type' => 'success',
                'message' => 'Accesses have been updated.'
            ]
        ]);
    }
}

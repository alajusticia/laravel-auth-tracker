<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthTrackingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all active logins for the current user.
     *
     * @param Request $request
     * @return mixed
     */
    public function listLogins(Request $request)
    {
        // Current user is tracked?
        if (method_exists($request->user(), 'logins')) {
            return view('auth.list', [
                'logins' => $request->user()->logins->sortByDesc('is_current')
            ]);
        }

        return redirect('/');
    }

    /**
     * Destroy a session / Revoke an access token by its ID.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logoutById(Request $request, $id)
    {
        $request->user()->logout($id);

        return $this->redirectToLoginsList();
    }

    /**
     * Destroy all sessions / Revoke all access tokens.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logoutAll(Request $request)
    {
        $request->user()->logoutAll();

        return redirect('/');
    }

    /**
     * Destroy all sessions / Revoke all access tokens, except the current one.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logoutOthers(Request $request)
    {
        $request->user()->logoutOthers();

        return $this->redirectToLoginsList();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToLoginsList()
    {
        return redirect()->route('login.list')->with([
            'status' => [
                'type' => 'success',
                'message' => 'Accesses have been updated.'
            ]
        ]);
    }
}

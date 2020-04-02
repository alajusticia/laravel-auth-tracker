<?php

namespace ALajusticia\AuthTracker\Traits;

use ALajusticia\AuthTracker\Models\Login;
use Illuminate\Database\Eloquent\Builder;

trait AuthTracking
{
    /**
     * Get all of the user's logins.
     */
    public function logins()
    {
        return $this->morphMany('ALajusticia\AuthTracker\Models\Login', 'authenticatable');
    }

    /**
     * Get the current user's login.
     *
     * @return Login|null
     */
    public function currentLogin()
    {
        if ($this->isAuthenticatedBySession()) {

            return $this->logins()
                        ->where('session_id', session()->getId())
                        ->first();

        } elseif ($this->isAuthenticatedByPassport()) {

            return $this->logins()
                        ->where('oauth_access_token_id', $this->token()->id)
                        ->first();

        } elseif ($this->isAuthenticatedBySanctum()) {

            return $this->logins()
                        ->where('personal_access_token_id', $this->currentAccessToken()->id)
                        ->first();

        }

        return null;
    }

    /**
     * Destroy a session / Revoke an access token by its ID.
     *
     * @param int|null $loginId
     * @return bool
     * @throws \Exception
     */
    public function logout($loginId = null)
    {
        $login = $loginId ? $this->logins()->find($loginId) : $this->currentLogin();

        return $login ? (! empty($login->revoke())) : false;
    }

    /**
     * Destroy all sessions / Revoke all access tokens, except the current one.
     *
     * @return mixed
     */
    public function logoutOthers()
    {
        if ($this->isAuthenticatedBySession()) {

            return $this->logins()
                        ->where(function (Builder $query) {
                            return $query
                                ->where('session_id', '!=', session()->getId())
                                ->orWhereNull('session_id');
                        })
                        ->revoke();

        } elseif ($this->isAuthenticatedByPassport()) {

            return $this->logins()
                        ->where(function (Builder $query) {
                            return $query
                                ->where('oauth_access_token_id', '!=', $this->token()->id)
                                ->orWhereNull('oauth_access_token_id');
                        })
                        ->revoke();

        } elseif ($this->isAuthenticatedBySanctum()) {

            return $this->logins()
                        ->where(function (Builder $query) {
                            return $query
                                ->where('personal_access_token_id', '!=', $this->currentAccessToken()->id)
                                ->orWhereNull('personal_access_token_id');
                        })
                        ->revoke();
        }

        return false;
    }

    /**
     * Destroy all sessions / Revoke all access tokens.
     *
     * @return mixed
     */
    public function logoutAll()
    {
        return $this->logins()->revoke();
    }

    /**
     * Determine if current user is authenticated via a session.
     *
     * @return bool
     */
    public function isAuthenticatedBySession()
    {
        return request()->hasSession();
    }

    /**
     * Check for authentication via Passport.
     *
     * @return bool
     */
    public function isAuthenticatedByPassport()
    {
        return in_array('Laravel\Passport\HasApiTokens', class_uses($this))
            && ! is_null($this->token());
    }

    /**
     * Check for authentication via Sanctum.
     *
     * @return bool
     */
    public function isAuthenticatedBySanctum()
    {
        return in_array('Laravel\Sanctum\HasApiTokens', class_uses($this))
            && ! is_null($this->currentAccessToken());
    }
}

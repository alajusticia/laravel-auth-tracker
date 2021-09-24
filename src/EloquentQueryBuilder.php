<?php

namespace ALajusticia\AuthTracker;

use ALajusticia\AuthTracker\Traits\ManagesLogins;
use Illuminate\Database\Eloquent\Builder;

class EloquentQueryBuilder extends Builder
{
    use ManagesLogins;

    /**
     * Revoke the logins.
     *
     * @return mixed
     */
    public function revoke()
    {
        $logins = $this->get();

        if ($logins->isNotEmpty()) {

            // Destroy sessions
            foreach ($logins->pluck('session_id')->filter() as $sessionId) {
                $this->destroySession($sessionId);
            }

            // Revoke Passport tokens
            $this->revokePassportTokens($logins->pluck('oauth_access_token_id')->filter());

            // Revoke Sanctum tokens
            $this->revokeSanctumTokens($logins->pluck('personal_access_token_id')->filter());

            // Delete logins
            return $this->delete();
        }

        return false;
    }
}

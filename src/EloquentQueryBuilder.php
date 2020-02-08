<?php

namespace ALajusticia\AuthTracker;

use ALajusticia\AuthTracker\Traits\ManagesLogins;
use ALajusticia\Expirable\ExpirableEloquentQueryBuilder;

class EloquentQueryBuilder extends ExpirableEloquentQueryBuilder
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

            // Revoke Airlock tokens
            $this->revokeAirlockTokens($logins->pluck('personal_access_token_id')->filter());

            // Delete logins
            return $this->delete();
        }

        return false;
    }
}

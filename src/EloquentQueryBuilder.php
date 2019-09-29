<?php

namespace AnthonyLajusticia\AuthTracker;

use AnthonyLajusticia\AuthTracker\Traits\ManagesLogins;
use AnthonyLajusticia\Expirable\ExpirableEloquentQueryBuilder;

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

            // Revoke tokens
            $this->revokeTokens($logins->pluck('oauth_access_token_id')->filter());

            // Delete logins
            return $this->delete();
        }

        return false;
    }
}

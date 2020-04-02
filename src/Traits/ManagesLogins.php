<?php

namespace ALajusticia\AuthTracker\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

trait ManagesLogins
{
    /**
     * Destroy the given session id.
     *
     * @param int $sessionId
     * @return void
     */
    protected function destroySession($sessionId)
    {
        if ($sessionId === session()->getId()) {
            Auth::logout();
            session()->invalidate();
        } else {
            session()->getHandler()->destroy($sessionId);
        }
    }

    /**
     * Revoke the given Passport access token ids.
     *
     * @param Collection|array|int $accessTokenIds
     * @return void
     */
    protected function revokePassportTokens($accessTokenIds)
    {
        // Support for collections
        if ($accessTokenIds instanceof Collection) {
            $accessTokenIds = $accessTokenIds->all();
        }

        // Convert parameters into an array if needed
        $accessTokenIds = is_array($accessTokenIds) ? $accessTokenIds : func_get_args();

        if (! empty($accessTokenIds)) {
            // Revoke refresh tokens
            DB::table('oauth_refresh_tokens')
                ->whereIn('access_token_id', $accessTokenIds)
                ->update(['revoked' => true]);

            // Revoke access tokens
            DB::table('oauth_access_tokens')
                ->whereIn('id', $accessTokenIds)
                ->update(['revoked' => true]);
        }
    }

    /**
     * Revoke the given Sanctum personal access token ids.
     *
     * @param Collection|array|int $personalAccessTokenIds
     * @return void
     */
    protected function revokeSanctumTokens($personalAccessTokenIds)
    {
        // Support for collections
        if ($personalAccessTokenIds instanceof Collection) {
            $personalAccessTokenIds = $personalAccessTokenIds->all();
        }

        // Convert parameters into an array if needed
        $personalAccessTokenIds = is_array($personalAccessTokenIds) ? $personalAccessTokenIds : func_get_args();

        if (! empty($personalAccessTokenIds)) {
            PersonalAccessToken::whereIn('id', $personalAccessTokenIds)
                ->delete();
        }
    }
}

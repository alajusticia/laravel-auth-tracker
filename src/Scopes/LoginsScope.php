<?php

namespace ALajusticia\AuthTracker\Scopes;

use ALajusticia\AuthTracker\Traits\ManagesLogins;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LoginsScope implements Scope
{
    use ManagesLogins;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('revoke', function (Builder $builder) {
            $logins = $builder->get();

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
                return $builder->delete();
            }

            return false;
        });
    }
}

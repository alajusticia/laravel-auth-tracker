<?php

namespace ALajusticia\AuthTracker;

use Illuminate\Auth\EloquentUserProvider;

class EloquentUserProviderExtended extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null|void
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        $retrievedModel = $this->newModelQuery($model)->where(
            $model->getAuthIdentifierName(), $identifier
        )->first();

        if (! $retrievedModel) {
            return;
        }

        $login = $retrievedModel->logins()->where('remember_token', $token)->first();

        return $login && hash_equals($login->remember_token, $token) ? $retrievedModel : null;
    }
}

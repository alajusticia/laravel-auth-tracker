<?php

namespace ALajusticia\AuthTracker\Events;

use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessTokenCreated
{
    use SerializesModels;

    /**
     * The newly created personal access token.
     *
     * @var PersonalAccessToken
     */
    public $personalAccessToken;

    /**
     * Create a new event instance.
     *
     * @param NewAccessToken $newAccessToken
     * @return void
     */
    public function __construct(NewAccessToken $newAccessToken)
    {
        $this->personalAccessToken = $newAccessToken->accessToken;
    }
}

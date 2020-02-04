<?php

namespace ALajusticia\AuthTracker\Events;

use ALajusticia\AuthTracker\RequestContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

class Login
{
    use SerializesModels;

    /**
     * The user.
     *
     * @var Authenticatable
     */
    public $user;

    /**
     * Informations about the request (user agent, ip address...).
     *
     * @var RequestContext
     */
    public $context;

    /**
     * Create a new event instance.
     *
     * @param RequestContext $context
     * @return void
     */
    public function __construct(Authenticatable $user, RequestContext $context)
    {
        $this->user = $user;
        $this->context = $context;
    }
}

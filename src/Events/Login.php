<?php

namespace AnthonyLajusticia\AuthTracker\Events;

use AnthonyLajusticia\AuthTracker\RequestContext;
use Illuminate\Queue\SerializesModels;

class Login
{
    use SerializesModels;

    public $context;

    /**
     * Create a new event instance.
     *
     * @param RequestContext $context
     * @return void
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }
}

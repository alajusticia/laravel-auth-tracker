<?php

namespace AnthonyLajusticia\AuthTracker\Events;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Queue\SerializesModels;

class FailedApiCall
{
    use SerializesModels;

    public $exception;

    /**
     * Create a new event instance.
     *
     * @param TransferException $exception
     * @return void
     */
    public function __construct(TransferException $exception)
    {
        $this->exception = $exception;
    }
}

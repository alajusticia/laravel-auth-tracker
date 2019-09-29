<?php

namespace AnthonyLajusticia\AuthTracker\Events;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class FailedApiCall
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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

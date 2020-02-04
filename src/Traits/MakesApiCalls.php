<?php

namespace ALajusticia\AuthTracker\Traits;

use ALajusticia\AuthTracker\Events\FailedApiCall;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

trait MakesApiCalls
{
    /**
     * @var Client $httpClient
     */
    protected $httpClient;

    /**
     * @var \Illuminate\Support\Collection|null
     */
    protected $result;

    /**
     * MakesApiCalls constructor.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        $this->httpClient = new Client([
            'connect_timeout' => config('auth_tracker.ip_lookup.timeout'),
        ]);

        $this->result = $this->makeApiCall();
    }

    /**
     * Make the API call and get the response as a Laravel collection.
     *
     * @return \Illuminate\Support\Collection|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function makeApiCall()
    {
        try {

            $response = $this->httpClient->send($this->getRequest());

            return collect(json_decode($response->getBody(), true));

        } catch (TransferException $e) {

            event(new FailedApiCall($e));

            return null;
        }
    }

    /**
     * Get the result of the API call as a Laravel collection.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getResult()
    {
        return $this->result;
    }
}

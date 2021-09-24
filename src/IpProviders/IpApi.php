<?php

namespace ALajusticia\AuthTracker\IpProviders;

use ALajusticia\AuthTracker\Interfaces\IpProvider;
use ALajusticia\AuthTracker\Traits\MakesApiCalls;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Request;

class IpApi implements IpProvider
{
    use MakesApiCalls;

    /**
     * Get the Guzzle request.
     *
     * @return GuzzleRequest
     */
    public function getRequest()
    {
        return new GuzzleRequest('GET', 'http://ip-api.com/json/' . Request::ip() . '?fields=25');
    }

    /**
     * Get the country name.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->result->get('country');
    }

    /**
     * Get the region name.
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->result->get('regionName');
    }

    /**
     * Get the city name.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->result->get('city');
    }
}

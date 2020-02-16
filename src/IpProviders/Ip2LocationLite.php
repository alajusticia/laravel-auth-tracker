<?php

namespace ALajusticia\AuthTracker\IpProviders;

use ALajusticia\AuthTracker\Interfaces\IpProvider;
use Illuminate\Support\Facades\DB;

class Ip2LocationLite implements IpProvider
{
    /**
     * @var object|null
     */
    protected $result;

    /**
     * Ip2LocationLite constructor.
     */
    public function __construct()
    {
        $table = filter_var(request()->ip(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                 ? config('auth_tracker.ip_lookup.ip2location.ipv6_table')
                 : config('auth_tracker.ip_lookup.ip2location.ipv4_table');

        $this->result = DB::table($table)
                          ->whereRaw('INET_ATON(?) <= ip_to', [request()->ip()])
                          ->first();
    }

    /**
     * Get the Guzzle request.
     *
     * @return void
     */
    public function getRequest()
    {
    }

    /**
     * Get the country name.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->result->country_name;
    }

    /**
     * Get the region name.
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->result->region_name;
    }

    /**
     * Get the city name.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->result->city_name;
    }

    /**
     * Get the result of the query.
     *
     * @return object|null
     */
    public function getResult()
    {
        return $this->result;
    }
}

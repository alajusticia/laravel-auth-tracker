<?php

namespace ALajusticia\AuthTracker;

use ALajusticia\AuthTracker\Factories\IpProviderFactory;
use ALajusticia\AuthTracker\Factories\ParserFactory;
use ALajusticia\AuthTracker\Interfaces\IpProvider;
use ALajusticia\AuthTracker\Interfaces\UserAgentParser;

class RequestContext
{
    /**
     * @var UserAgentParser $parser
     */
    protected $parser;

    /**
     * @var IpProvider $ipProvider
     */
    protected $ipProvider = null;

    /**
     * @var string $userAgent
     */
    public $userAgent;

    /**
     * @var string|null $ip
     */
    public $ip;

    /**
     * RequestContext constructor.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        // Initialize the parser
        $this->parser = ParserFactory::build(config('auth_tracker.parser'));

        // Initialize the IP provider
        $this->ipProvider = IpProviderFactory::build(config('auth_tracker.ip_lookup.provider'));

        $this->userAgent = request()->userAgent();
        $this->ip = request()->ip();
    }

    /**
     * Get the parser used to parse the User-Agent header.
     *
     * @return UserAgentParser
     */
    public function parser()
    {
        return $this->parser;
    }

    /**
     * Get the IP lookup result.
     *
     * @return IpProvider
     */
    public function ip()
    {
        if ($this->ipProvider && $this->ipProvider->getResult()) {
            return $this->ipProvider;
        }

        return null;
    }
}

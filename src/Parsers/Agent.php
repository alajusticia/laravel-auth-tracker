<?php

namespace ALajusticia\AuthTracker\Parsers;

use ALajusticia\AuthTracker\Interfaces\UserAgentParser;
use Jenssegers\Agent\Agent as Parser;

class Agent implements UserAgentParser
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * Agent constructor.
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Get the device name.
     *
     * @return string|null
     */
    public function getDevice()
    {
        $device = $this->parser->device();

        return $device && $device !== 'WebKit' ? $device : null;
    }

    /**
     * Get the device type.
     *
     * @return string|null
     */
    public function getDeviceType()
    {
        if ($this->parser->isDesktop()) {
            return 'desktop';
        } elseif ($this->parser->isMobile()) {
            return $this->parser->isTablet() ? 'tablet' : ($this->parser->isPhone() ? 'phone' : 'mobile');
        }

        return null;
    }

    /**
     * Get the platform name.
     *
     * @return string|null
     */
    public function getPlatform()
    {
        return $this->parser->platform() ?: null;
    }

    /**
     * Get the browser name.
     *
     * @return string|null
     */
    public function getBrowser()
    {
        return $this->parser->browser() ?: null;
    }
}

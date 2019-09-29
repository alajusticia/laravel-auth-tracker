<?php

namespace AnthonyLajusticia\AuthTracker\Parsers;

use AnthonyLajusticia\AuthTracker\Interfaces\UserAgentParser;
use WhichBrowser\Parser;

class WhichBrowser implements UserAgentParser
{
    protected $parser;

    public function __construct()
    {
        $this->parser = new Parser(request()->userAgent());
    }

    /**
     * Get the device name.
     *
     * @return string
     */
    public function getDevice()
    {
        return ! empty($this->parser->device->toString()) ?
            $this->parser->device->toString() :
            $this->parser->device->getManufacturer().' '.$this->parser->device->getModel();
    }

    /**
     * Get the device type.
     *
     * @return string
     */
    public function getDeviceType()
    {
        return ucfirst($this->parser->device->type);
    }

    /**
     * Get the platform name.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->parser->os->toString();
    }

    /**
     * Get the browser name.
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->parser->browser->name;
    }
}

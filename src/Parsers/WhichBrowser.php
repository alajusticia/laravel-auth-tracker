<?php

namespace ALajusticia\AuthTracker\Parsers;

use ALajusticia\AuthTracker\Interfaces\UserAgentParser;
use Illuminate\Support\Facades\Request;
use WhichBrowser\Parser;

class WhichBrowser implements UserAgentParser
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * WhichBrowser constructor.
     */
    public function __construct()
    {
        $this->parser = new Parser(Request::userAgent());
    }

    /**
     * Get the device name.
     *
     * @return string|null
     */
    public function getDevice()
    {
        return trim($this->parser->device->toString()) ?: $this->getDeviceByManufacturerAndModel();
    }

    /**
     * Get the device name by manufacturer and model.
     *
     * @return string|null
     */
    protected function getDeviceByManufacturerAndModel()
    {
        return trim($this->parser->device->getManufacturer().' '.$this->parser->device->getModel()) ?: null;
    }

    /**
     * Get the device type.
     *
     * @return string|null
     */
    public function getDeviceType()
    {
        return trim($this->parser->device->type) ?: null;
    }

    /**
     * Get the platform name.
     *
     * @return string|null
     */
    public function getPlatform()
    {
        return trim($this->parser->os->toString()) ?: null;
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

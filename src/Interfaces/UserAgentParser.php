<?php

namespace AnthonyLajusticia\AuthTracker\Interfaces;

interface UserAgentParser
{
    /**
     * Get the device name.
     *
     * @return string
     */
    public function getDevice();

    /**
     * Get the device type.
     *
     * @return string
     */
    public function getDeviceType();

    /**
     * Get the platform name.
     *
     * @return string
     */
    public function getPlatform();

    /**
     * Get the browser name.
     *
     * @return string
     */
    public function getBrowser();
}

<?php

namespace Soluble\Japha\Bridge\Driver;

interface DriverInterface
{
    /**
     * Return a new java class
     *
     * @param string $class_name
     */
    function getJavaClass($class_name);
}

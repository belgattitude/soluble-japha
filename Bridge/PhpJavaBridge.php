<?php

namespace Soluble\Japha\Bridge;

use Soluble\Japha\Bridge\Driver;

class PhpJavaBridge
{

    const DRIVER_PJB621 = 'Pjb621';

    /**
     * @var array
     */
    protected static $drivers = array(
        self::DRIVER_PJB621 => 'Soluble\Japha\Bridge\Driver\Pjb621\Pjb621Driver'
    );
    protected static $default_driver = self::DRIVER_PJB621;

    /**
     * @var array
     */
    protected static $driver_loaded = array(
        self::DRIVER_PJB621 => false
    );

    /**
     *
     * @var Driver\AbstractDriver
     */
    protected static $driver;

    /**
     * Include remote javabridge and check if it's available
     *
     * @throws Exception\NotAvailableException
     * @throws Exception\UnsupportedDriverException
     * @param string $server_url
     * @param string $driver
     * @return void
     */
    public static function includeBridge($server_url, $driver = null)
    {
        if ($driver === null) {
            $driver = self::$default_driver;
        }

        if (!isset(self::$drivers[$driver])) {
            throw new Exception\UnsupportedDriverException(__METHOD__ . " Unsupported driver '$driver'");
        }

        if (!self::$driver_loaded[$driver]) {
            $driver_class = self::$drivers[$driver];
            self::$driver = new $driver_class($server_url);

            self::$driver_loaded[$driver] = true;
        }
    }

    /**
     *
     * @return Driver\AbstractDriver
     * @throws Exception\InvalidUsageException
     */
    public static function getDriver()
    {
        if (self::$driver === null) {
            throw new Exception\InvalidUsageException(__METHOD__ . " PhpJavaBridge must be loaded prior to getDriver.");
        }
        return self::$driver;
    }

    /**
     *
     * @param string $class
     */
    public static function getJavaClass($class)
    {
        return self::getDriver()->getJavaClass($class);
    }
    
    /**
     *
     * @param string $class
     */
    public static function instanciate($class, $arguments)
    {
        return self::getDriver()->instanciate($class, $arguments);
    }
}

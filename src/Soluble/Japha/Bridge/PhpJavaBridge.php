<?php

namespace Soluble\Japha\Bridge;

use Soluble\Japha\Bridge\Driver;
use Soluble\Japha\Interfaces;

class PhpJavaBridge
{
    const DRIVER_Pjb62 = 'Pjb62';

    /**
     * @var array
     */
    protected static $drivers = array(
        self::DRIVER_Pjb62 => 'Soluble\Japha\Bridge\Driver\Pjb62\Pjb62Driver'
    );
    protected static $default_driver = self::DRIVER_Pjb62;

    /**
     * @var array
     */
    protected static $driver_loaded = array(
        self::DRIVER_Pjb62 => false
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
            self::$driver = new $driver_class(array('java_server_url' => $server_url));
            self::$driver_loaded[$driver] = true;
        }
    }

    /**
     * @throws Exception\InvalidUsageException
     * @return Driver\AbstractDriver
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
     * @return Interfaces\JavaClass
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

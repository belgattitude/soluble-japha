<?php

declare(strict_types=1);

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace Soluble\Japha\Bridge;

use Soluble\Japha\Interfaces;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Adapter
{
    /**
     * @var array
     */
    protected static $registeredDrivers = [
        'pjb62' => 'Soluble\Japha\Bridge\Driver\Pjb62\Pjb62Driver'
    ];

    /**
     * @var Driver\AbstractDriver
     */
    protected $driver;

    /**
     * @var Adapter\System
     */
    protected $system;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * <code>
     * $ba = new Adapter([
     *     'driver' => 'Pjb62',
     *     'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *      //'java_default_timezone' => null,
     *      //'java_prefer_values' => true,
     *      //'force_simple_xml_parser' => false
     *    ]);
     *
     * </code>
     *
     *
     * @throws Exception\UnsupportedDriverException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ConfigurationException
     * @throws Exception\ConnectionException
     *
     * @param array           $options
     * @param LoggerInterface $logger  any PSR-3 compatible logger
     */
    public function __construct(array $options, LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;

        $driver = strtolower($options['driver']);
        if (!array_key_exists($driver, self::$registeredDrivers)) {
            throw new Exception\UnsupportedDriverException(__METHOD__ . "Driver '$driver' is not supported");
        }

        $driver_class = self::$registeredDrivers[$driver];
        $this->driver = new $driver_class($options, $logger);

        if (array_key_exists('java_default_timezone', $options)
            && $options['java_default_timezone'] !== null) {
            $this->setJavaDefaultTimezone($options['java_default_timezone']);
        }
    }

    /**
     * Create a new Java instance from a FQDN (constructor arguments are sent in a variadic way).
     *
     * <code>
     * $hash   = $ba->java('java.util.HashMap', ['key' => '保éà']);
     * echo $hash->get('key'); // prints "保éà"
     * </code>
     *
     * @param string     $class   Java class name (FQDN)
     * @param mixed|null ...$args arguments passed to the constructor of the java object
     *
     * @throws \Soluble\Japha\Bridge\Exception\JavaException
     * @throws \Soluble\Japha\Bridge\Exception\ClassNotFoundException
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @see Adapter::javaClass($class) for information about classes
     *
     * @return Interfaces\JavaObject
     */
    public function java(string $class, ...$args): Interfaces\JavaObject
    {
        return $this->driver->instanciate($class, ...$args);
    }

    /**
     * Load a java class.
     *
     * <code>
     * $calendar = $ba->javaClass('java.util.Calendar')->getInstance();
     * $date = $calendar->getTime();
     *
     * $system = $ba->javaClass('java.lang.System');
     * echo  $system->getProperties()->get('java.vm_name);
     *
     * $tzClass = $ba->javaClass('java.util.TimeZone');
     * echo $tz->getDisplayName(false, $tzClass->SHORT);
     * </code>
     *
     * @see Adapter::java($class, $args) for object creation
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     * @throws \Soluble\Japha\Bridge\Exception\ClassNotFoundException
     *
     * @param string $class Java class name (FQDN)
     *
     * @return Interfaces\JavaClass
     */
    public function javaClass(string $class): Interfaces\JavaClass
    {
        return $this->driver->getJavaClass($class);
    }

    /**
     * Checks whether object is an instance of a class or interface.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     * @throws \Soluble\Japha\Bridge\Exception\ClassNotFoundException
     * @throws \Soluble\Japha\Bridge\Exception\InvalidArgumentException
     *
     * @param Interfaces\JavaObject                             $javaObject
     * @param string|Interfaces\JavaObject|Interfaces\JavaClass $className  java class name
     *
     * @return bool
     */
    public function isInstanceOf(Interfaces\JavaObject $javaObject, $className): bool
    {
        return $this->driver->isInstanceOf($javaObject, $className);
    }

    /**
     * Return object java FQDN class name.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return string
     */
    public function getClassName(Interfaces\JavaObject $javaObject): string
    {
        return $this->driver->getClassName($javaObject);
    }

    /**
     * Whether a java internal value is null.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject|null $javaObject
     *
     * @return bool
     */
    public function isNull(Interfaces\JavaObject $javaObject = null): bool
    {
        return $this->driver->isNull($javaObject);
    }

    /**
     * Check wether a java value is true (boolean).
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject|null $javaObject
     *
     * @return bool
     */
    public function isTrue(Interfaces\JavaObject $javaObject): bool
    {
        return $this->driver->isTrue($javaObject);
    }

    /**
     * Return system properties.
     *
     * @return Adapter\System
     */
    public function getSystem(): Adapter\System
    {
        if ($this->system === null) {
            $this->system = new Adapter\System($this);
        }

        return $this->system;
    }

    /**
     * Fast retrieval of JavaObject values (one roundtrip),
     * use it on Java array structures (ArrayList, HashMap...)
     * to avoid the need of iterations on the PHP side.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return mixed
     */
    public function values(Interfaces\JavaObject $javaObject)
    {
        return $this->driver->values($javaObject);
    }

    /**
     * Return underlying driver.
     *
     * @return Driver\DriverInterface
     */
    public function getDriver(): Driver\DriverInterface
    {
        return $this->driver;
    }

    /**
     * Set the JVM/Java default timezone.
     *
     * Caution: this method should be used with care because it will change the global timezone
     * on the JVM or Bridge servlet. Better to configure it by other ways as every
     * scripts may change it.
     *
     *
     * @throws \Soluble\Japha\Util\Exception\UnsupportedTzException
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param string $timezone
     */
    protected function setJavaDefaultTimezone(string $timezone): void
    {
        $this->getSystem()->setTimeZoneId($timezone);
    }
}

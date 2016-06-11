<?php

namespace Soluble\Japha\Bridge;

use Soluble\Japha\Interfaces;
use Soluble\Japha\Util\Exception\UnsupportedTzException;

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
     *
     * @var Adapter\System
     */
    protected $system;


    /**
     * Constructor
     *
     * <code>
     *
     * $ba = new Adapter([
     *     'driver' => 'Pjb62',
     *     'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *      //'java_default_timezone' => null,
     *      //'java_disable_autoload' => false,
     *      //'java_prefer_values' => true,
     *      //'load_pjb_compatibility' => false
     *    ]);
     *
     * </code>
     *
     *
     * @throws Exception\UnsupportedDriverException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ConfigurationException
     *
     * @param array options
     *
     */
    public function __construct(array $options)
    {
        $driver = strtolower($options['driver']);
        if (!array_key_exists($driver, self::$registeredDrivers)) {
            throw new Exception\UnsupportedDriverException(__METHOD__ . "Driver '$driver' is not supported");
        }

        $driver_class = self::$registeredDrivers[$driver];
        $this->driver = new $driver_class($options);

        $tz = array_key_exists('java_default_timezone', $options) ? $options['java_default_timezone'] : null;
        if ($tz !== null) {
            $this->setJavaDefaultTimezone($tz);
        }
    }


    /**
     * Instanciate a new java object
     *
     * <code>
     * $hash   = $ba->java('java.util.HashMap', array('my_key' => 'my_value'));
     * echo $hash->get('new_key'); // prints "保éà"
     * </code>
     *
     * @param string $class Java class name (FQDN)
     * @param mixed|null $args arguments passed to the constructor of the java object
     *
     * @throws \Soluble\Japha\Bridge\Exception\JavaException
     * @throws \Soluble\Japha\Bridge\Exception\ClassNotFoundException
     *
     * @see Adapter\javaClass($class) for information about classes
     *
     * @return Interfaces\JavaObject
     */
    public function java($class, $args = null)
    {
        return $this->driver->instanciate($class, $args);
    }

    /**
     * Load a java class
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
     * @param string $class Java class name (FQDN)
     *
     * @see Adapter\java($class, $args) for object creation
     *
     * @param string $class
     * @return Interfaces\JavaClass
     */
    public function javaClass($class)
    {
        return $this->driver->getJavaClass($class);
    }


    /**
     * Checks whether object is an instance of a class or interface
     *
     * @param Interfaces\JavaObject $javaObject
     * @param string|Interfaces\JavaObject $className java class name
     * @return boolean
     */
    public function isInstanceOf(Interfaces\JavaObject $javaObject, $className)
    {
        return $this->driver->isInstanceOf($javaObject, $className);
    }

    /**
     * Whether a java internal value is null
     *
     * @param Interfaces\JavaObject|null $javaObject
     * @return boolean
     */
    public function isNull(Interfaces\JavaObject $javaObject = null)
    {
        return $this->driver->isNull($javaObject);
    }

    /**
     * Check wether a java value is true (boolean)
     *
     * @param Interfaces\JavaObject|null $javaObject
     * @return boolean
     */
    public function isTrue(Interfaces\JavaObject $javaObject)
    {
        return $this->driver->isTrue($javaObject);
    }


    /**
     * Return system properties
     *
     * @return Adapter\System
     */
    public function getSystem()
    {
        if ($this->system === null) {
            $this->system = new Adapter\System($this);
        }
        return $this->system;
    }

    /**
     * Return underlying driver
     *
     * @return Driver\AbstractDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set the JVM/Java default timezone
     *
     * @throws Exception\ConfigurationException
     * @throws UnsupportedTzException
     *
     * @param string $timezone
     */
    protected function setJavaDefaultTimezone($timezone = null)
    {
        if ($timezone == '') {
            $phpTz = date_default_timezone_get();

            // In case there's a mismatch between PHP and Java see also :
            // - date('T');
            // - http://php.net/manual/en/datetimezone.listabbreviations.php

            if ($phpTz == '') {
                $message = 'Japha\Bridge requires a valid php default timezone set prior to run';
                $message .= ', check you php configuration ini settings "date.timezone" or';
                $message .= ' set it with "date_default_timezone_set" ';
                $message .= ' or provide a "java_default_timezone" in the adapter options.';
                throw new Exception\ConfigurationException($message);
            }

            $timezone = $phpTz;
        }

        $this->getSystem()->setTimeZoneId($timezone);
    }
}

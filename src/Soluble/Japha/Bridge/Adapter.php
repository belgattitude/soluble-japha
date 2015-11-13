<?php

namespace Soluble\Japha\Bridge;

use Soluble\Japha\Interfaces;

/**
 *
  class Foo {
        function toString() {return "php::foo";}
  }
  $foo = new Foo();
  $jObj = java_closure($foo);
  $String = java("java.lang.String");
  echo $String->valueOf($jObj);|
 *
 */

class Adapter
{
    /**
     * @var array
     */
    protected static $registeredDrivers = array(
        'pjb62' => 'Soluble\Japha\Bridge\Driver\Pjb62\Pjb62Driver'
    );


    /**
     * @var Driver\AbstractDriver
     */
    protected $driver;


    /**
     * Constructor
     *
     * <code>
     *
     * $ba = new Adapter([
     *     'driver' => 'Pjb62',
     *     'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *      //"java_disable_autoload' => false,
     *      //"java_prefer_values' => true,
     *      //"load_pjb_compatibility' => false
     *    ]);
     *
     * </code>

     *  ));
     *
     * </code>
     *
     * @throws Exception\UnsupportedDriverException
     * @throws Exception\InvalidArgumentException
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
     * Instanciate a new java object
     *
     * @param string $class Java class name (FQDN)
     * @param mixed|null $args arguments passed to the constructor of the java object
     *
     * @throws Soluble\Japha\Bridge\Exception\JavaException
     * @throws Soluble\Japha\Bridge\Exception\ClassNotFoundException
     *
     * @see Adapter\javaClass($class)
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
     * @param string $class Java class name (FQDN)
     *
     * @see Adapter\java($class, $args)
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
}

<?php

namespace Soluble\Japha\Bridge\Driver;

use Soluble\Japha\Interfaces;

abstract class AbstractDriver implements DriverInterface, ConnectionInterface
{
    /**
     *
     *
     * @param Interfaces\JavaObject $javaObject
     * @return mixed
     */
    abstract public function values(Interfaces\JavaObject $javaObject);
    
    /**
     * Inspect object
     *
     * @param Interfaces\JavaObject $javaObject
     * @return string
     */
    abstract public function inspect(Interfaces\JavaObject $javaObject);
    
    
    /**
     * Whether object is an instance of specific java class or interface
     *
     * @param Interfaces\JavaObject $javaObject
     * @param string|Interfaces\JavaClass|Interfaces\JavaObject $className java class or interface name
     * @return boolean
     */
    abstract public function isInstanceOf(Interfaces\JavaObject $javaObject, $className);
    
    /**
     * Return object java class name
     *
     * @param Interfaces\JavaObject $javaObject
     * @return string
     */
    abstract public function getClassName(Interfaces\JavaObject $javaObject);
    
    
    /**
     * Return a new java class
     *
     * @param string $class_name
     * @return
     */
    abstract public function getJavaClass($class_name);
    
    /**
     * Instanciate a java object
     *
     * @throws Exception\ClassFoundException
     * @param string $class_name
     * @param mixed|null $args
     * @return Java
     */
    abstract public function instanciate($class_name, $args = null);
}

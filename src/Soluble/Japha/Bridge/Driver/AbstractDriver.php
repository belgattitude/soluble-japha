<?php

namespace Soluble\Japha\Bridge\Driver;

use Soluble\Japha\Interfaces;

abstract class AbstractDriver implements DriverInterface, ConnectionInterface
{
    const CAST_TYPE_STRING  = 'string';
    const CAST_TYPE_BOOLEAN = 'boolean';
    const CAST_TYPE_INTEGER = 'integer';
    const CAST_TYPE_FLOAT   = 'float';
    const CAST_TYPE_ARRAY   = 'array';
    const CAST_TYPE_NULL    = 'null';
    const CAST_TYPE_OBJECT  = 'object';

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
     * @return Interfaces\JavaClass
     */
    abstract public function getJavaClass($class_name);

    /**
     * Instanciate a java object
     *
     * @throws Exception\ClassFoundException
     * @param string $class_name
     * @param mixed|null $args
     * @return Interfaces\JavaObject
     */
    abstract public function instanciate($class_name, $args = null);

    /**
     * Cast a java object into a php type
     *
     * @see self::CAST_TYPE_*
     *
     * @throws Exception\RuntimeException
     *
     * @param Interfaces\JavaObject $javaObject
     * @param string $cast_type
     * @return mixed
     */
    abstract public function cast(Interfaces\JavaObject $javaObject, $cast_type);
}

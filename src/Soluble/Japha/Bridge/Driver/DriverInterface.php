<?php

namespace Soluble\Japha\Bridge\Driver;

use Psr\Log\LoggerInterface;
use Soluble\Japha\Interfaces;

interface DriverInterface
{

    /**
     * DriverInterface constructor.
     * @param array $options
     * @param LoggerInterface $logger
     */
    public function __construct(array $options, LoggerInterface $logger=null);

    /**
     * Return a new java class
     *
     * @param string $class_name
     * @return Interfaces\JavaClass
     */
    public function getJavaClass($class_name);

    /**
     * Whether object is an instance of specific java class or interface
     *
     * @param Interfaces\JavaObject $javaObject
     * @param string|Interfaces\JavaClass|Interfaces\JavaObject $className java class or interface name
     * @return boolean
     */
    public function isInstanceOf(Interfaces\JavaObject $javaObject, $className);

    /**
     * Return object java class name
     *
     * @param Interfaces\JavaObject $javaObject
     * @return string
     */
    public function getClassName(Interfaces\JavaObject $javaObject);

    /**
     * Instanciate a java object
     *
     * @throws Exception\ClassFoundException
     * @param string $class_name
     * @param mixed|null $args
     * @return Interfaces\JavaObject
     */
    public function instanciate($class_name, $args = null);
}

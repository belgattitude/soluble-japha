<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace Soluble\Japha\Bridge\Driver;

use Psr\Log\LoggerInterface;
use Soluble\Japha\Interfaces;

interface DriverInterface extends ConnectionInterface
{
    /**
     * DriverInterface constructor.
     *
     * @param array           $options
     * @param LoggerInterface $logger
     */
    public function __construct(array $options, LoggerInterface $logger = null);

    /**
     * Instanciate a new java object.
     *
     * @throws \Soluble\Japha\Bridge\Exception\ClassFoundException
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param string     $class_name Java FQDN i.e: 'java.lang.String'
     * @param mixed|null ...$args    arguments as variadic notation
     *
     * @return Interfaces\JavaObject
     */
    public function instanciate($class_name, ...$args);

    /**
     * Return a new java class.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     * @throws \Soluble\Japha\Bridge\Exception\ClassNotFoundException
     *
     * @param string $class_name Java class FQDN i.e: 'java.lang.String'
     *
     * @return Interfaces\JavaClass
     */
    public function getJavaClass($class_name);

    /**
     * Whether object is an instance of specific java class or interface.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     * @throws \Soluble\Japha\Bridge\Exception\ClassNotFoundException
     *
     * @param Interfaces\JavaObject                             $javaObject
     * @param string|Interfaces\JavaClass|Interfaces\JavaObject $className  java class or interface name
     *
     * @return bool
     */
    public function isInstanceOf(Interfaces\JavaObject $javaObject, $className);

    /**
     * Return object java class name.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return string
     */
    public function getClassName(Interfaces\JavaObject $javaObject);

    /**
     * Inspect object.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return string
     */
    public function inspect(Interfaces\JavaObject $javaObject);

    /**
     * Invoke a method on a JavaObject (or a static method on a JavaClass).
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaType $javaObject javaObject can be Interfaces\JavaClass or Interfaces\JavaObject, if null use servlet methods registered on th JavaBridge side
     * @param string              $method     Method name on the JavaObject or JavaClass
     * @param array               $args       arguments
     *
     * @return mixed
     */
    public function invoke(Interfaces\JavaType $javaObject = null, $method, array $args = []);

    /**
     * Check whether a java value is null.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return bool
     */
    public function isNull(Interfaces\JavaObject $javaObject = null);

    /**
     * Check whether a java value is true (boolean and int values are considered).
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return bool
     */
    public function isTrue(Interfaces\JavaObject $javaObject);

    /**
     * Returns the jsr223 script context handle.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @return Interfaces\JavaObject
     */
    public function getContext();

    /**
     * One round trip retrieval of Java object value representation.
     *
     * @throws \Soluble\Japha\Bridge\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return mixed
     */
    public function values(Interfaces\JavaObject $javaObject);
}

<?php

namespace Soluble\Japha\Interfaces;

/**
 * @method mixed __cast(string $type)
 */
interface JavaObject extends JavaType, \ArrayAccess, \IteratorAggregate
{
    /**
     * Returns the runtime class of this Object.
     * The returned Class object is the object that is locked by static synchronized methods of the represented class.
     *
     * @return JavaObject Java('java.lang.Class')
     */
    public function getClass();

    /**
     * Delegate the magic method __get() to the java object
     * to access the Java object properties (and not the PHP
     * remote proxied object).
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key);

    /**
     * Delegate the magic method __set() to the java object
     * to access the Java object properties (and not the PHP
     * remote proxied object).
     *
     * @param string $key
     * @param mixed  $val
     */
    public function __set($key, $val);

    /**
     * Call a java method on the JavaObject (delegated to Java object).
     *
     * As Java methods are not known on the PHP side, whenever you call
     * a method that is not defined on the PHP object it will be
     * delegated to the JVM through the bridge.
     *
     * @throws \Soluble\Japha\Bridge\Exception\JavaException
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments);

    /**
     * Delegate the magic method __toString() to the java object
     * to get the JavaObject as string.
     *
     * @return string
     */
    public function __toString();
}

<?php

namespace Soluble\Japha\Bridge\Driver;

use Soluble\Japha\Bridge\JavaObjectInterface;

abstract class AbstractDriver implements DriverInterface, ConnectionInterface
{

    
    /**
     *
     *
     * @param JavaObjectInterface $javaObject
     * @return mixed
     */
    abstract function values(JavaObjectInterface $javaObject);
    
    /**
     * Inspect object
     *
     * @param JavaObjectInterface $javaObject
     * @return string
     */
    abstract function inspect(JavaObjectInterface $javaObject);
    
    
    /**
     * Whether object is an instance of specific java class
     *
     * @param JavaObjectInterface $javaObject
     * @param string $className
     * @return boolean
     */
    abstract function isInstanceOf(JavaObjectInterface $javaObject, $className);
    
    /**
     * Return object java class name
     *
     * @param JavaObjectInterface $javaObject
     * @return string
     */
    abstract function getClassName(JavaObjectInterface $javaObject);
}

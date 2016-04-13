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
     * {@inheritdoc}
     */
    abstract public function isInstanceOf(Interfaces\JavaObject $javaObject, $className);


    /**
     * {@inheritdoc}
     */
    abstract public function getClassName(Interfaces\JavaObject $javaObject);


    /**
     * {@inheritdoc}
     */
    abstract public function getJavaClass($class_name);

    /**
     * {@inheritdoc}
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

    /**
     * Check wether a java value is null
     *
     * @param Interfaces\JavaObject|null $javaObject
     * @return boolean
     */
    public function isNull(Interfaces\JavaObject $javaObject = null)
    {
        if ($javaObject === null) {
            return true;
        }
        return ($this->values($javaObject) === null);
    }


    /**
     * Check wether a java value is true (boolean and int values are considered)
     *
     * @param Interfaces\JavaObject $javaObject
     * @return boolean
     */
    public function isTrue(Interfaces\JavaObject $javaObject)
    {
        $values = $this->values($javaObject);
        if (is_int($values) || is_bool($values)) {
            return ($values == true);
        }
        return false;
    }
}

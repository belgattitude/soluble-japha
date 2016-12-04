<?php

namespace Soluble\Japha\Bridge\Driver;

use Soluble\Japha\Interfaces;

abstract class AbstractDriver implements DriverInterface
{
    const CAST_TYPE_STRING = 'string';
    const CAST_TYPE_BOOLEAN = 'boolean';
    const CAST_TYPE_INTEGER = 'integer';
    const CAST_TYPE_FLOAT = 'float';
    const CAST_TYPE_ARRAY = 'array';
    const CAST_TYPE_NULL = 'null';
    const CAST_TYPE_OBJECT = 'object';

    /**
     * {@inheritdoc}
     */
    abstract public function instanciate($class_name, $args = null);

    /**
     * @param Interfaces\JavaObject $javaObject
     *
     * @return mixed
     */
    abstract public function values(Interfaces\JavaObject $javaObject);

    /**
     * Inspect object.
     *
     * @param Interfaces\JavaObject $javaObject
     *
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
    abstract public function invoke(Interfaces\JavaType $javaObject, $method, array $args = []);

    /**
     * Return Java servlet context.
     *
     * <code>
     *
     * </code>
     *
     * @return Interfaces\JavaObject
     */
    abstract public function getContext();

    /**
     * Return java servlet session.
     *
     * <code>
     * $session = $adapter->getDriver()->getJavaSession();
     * $counter = $session->get('counter');
     * if ($adapter->isNull($counter)) {
     *    $session->put('counter', 1);
     * } else {
     *    $session->put('counter', $counter + 1);
     * }
     * </code>
     *
     * @param array $args
     *
     * @return Interfaces\JavaObject
     */
    abstract public function getJavaSession(array $args = []);

    /**
     * Cast a java object into a php type.
     *
     * @see self::CAST_TYPE_*
     *
     * @throws \Soluble\Japha\Bridge\Exception\RuntimeException
     *
     * @param Interfaces\JavaObject $javaObject
     * @param string                $cast_type
     *
     * @return mixed
     */
    abstract public function cast(Interfaces\JavaObject $javaObject, $cast_type);

    /**
     * Check whether a java value is null.
     *
     * @param Interfaces\JavaObject|null $javaObject
     *
     * @return bool
     */
    public function isNull(Interfaces\JavaObject $javaObject = null)
    {
        if ($javaObject === null) {
            return true;
        }

        return $this->values($javaObject) === null;
    }

    /**
     * Check wether a java value is true (boolean and int values are considered).
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return bool
     */
    public function isTrue(Interfaces\JavaObject $javaObject)
    {
        $values = $this->values($javaObject);
        if (is_int($values) || is_bool($values)) {
            return $values == true;
        }

        return false;
    }
}

<?php

declare(strict_types=1);

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

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
    abstract public function instanciate(string $class_name, ...$args): Interfaces\JavaObject;

    /**
     * Fast retrieval of JavaObject values (one roundtrip),
     * use it on Java array structures (ArrayList...)
     * to avoid the need of iterations on the PHP side.
     *
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
    abstract public function inspect(Interfaces\JavaObject $javaObject): string;

    /**
     * {@inheritdoc}
     */
    abstract public function isInstanceOf(Interfaces\JavaObject $javaObject, $className): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function getClassName(Interfaces\JavaObject $javaObject): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getJavaClass(string $class_name): Interfaces\JavaClass;

    /**
     * {@inheritdoc}
     */
    abstract public function invoke(Interfaces\JavaType $javaObject = null, string $method, array $args = []);

    /**
     * {@inheritdoc}
     */
    abstract public function getContext(): Interfaces\JavaObject;

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
    abstract public function getJavaSession(array $args = []): Interfaces\JavaObject;

    /**
     * Cast a java object into a php type.
     *(see self::CAST_TYPE_*).
     *
     * @throws \Soluble\Japha\Bridge\Exception\RuntimeException
     *
     * @param Interfaces\JavaObject|int|float|array|bool $javaObject
     * @param string                                     $cast_type
     *
     * @return mixed
     */
    abstract public function cast($javaObject, string $cast_type);

    /**
     * {@inheritdoc}
     */
    public function isNull(Interfaces\JavaObject $javaObject = null): bool
    {
        if ($javaObject === null) {
            return true;
        }

        return $this->values($javaObject) === null;
    }

    /**
     * {@inheritdoc}
     */
    public function isTrue(Interfaces\JavaObject $javaObject): bool
    {
        $values = $this->values($javaObject);
        if (is_int($values) || is_bool($values)) {
            return $values == true;
        }

        return false;
    }
}

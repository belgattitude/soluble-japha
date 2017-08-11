<?php

declare(strict_types=1);
/**
 * soluble-japha / PHPJavaBridge driver client.
 *
 * Refactored version of phpjababridge's Java.inc file compatible
 * with php java bridge 6.2
 *
 *
 * @credits   http://php-java-bridge.sourceforge.net/pjb/
 *
 * @see      http://github.com/belgattitude/soluble-japha
 *
 * @author Jost Boekemeier
 * @author Vanvelthem Sébastien (refactoring and fixes from original implementation)
 * @license   MIT
 *
 * The MIT License (MIT)
 * Copyright (c) 2014-2017 Jost Boekemeier
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @method string getName()
 * @method string forName()
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Interfaces;

abstract class AbstractJava implements \IteratorAggregate, \ArrayAccess, JavaType, Interfaces\JavaObject
{
    /**
     * @var Client
     */
    public $__client;

    /**
     * @var JavaProxy|JavaType
     */
    public $__delegate;
    protected $__serialID;
    public $__factory;

    /**
     * @var int
     */
    public $__java;
    public $__signature;
    public $__cancelProxyCreationTag;

    protected function __createDelegate(): void
    {
        $proxy = $this->__factory->create($this->__java, $this->__signature);
        $this->__delegate = $proxy;
        $this->__java = $proxy->__java;
        $this->__signature = $proxy->__signature;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function __cast(string $type)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__cast($type);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        $this->__delegate->__sleep();

        return ['__delegate'];
    }

    public function __wakeup()
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        $this->__delegate->__wakeup();
        $this->__java = $this->__delegate->get__java();
        $this->__client = $this->__delegate->get__signature();
    }

    /**
     * Delegate the magic method __get() to the java object
     * to access the Java object properties (and not the PHP
     * remote proxied object).
     *
     * @throws \Exception Depending on ThrowExceptionProxy
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__get($key);
    }

    /**
     * Delegate the magic method __set() to the java object
     * to access the Java object properties (and not the PHP
     * remote proxied object).
     *
     * @throws \Exception Depending on ThrowExceptionProxy
     *
     * @param string $key
     * @param mixed  $val
     */
    public function __set(string $key, $val): void
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        $this->__delegate->__set($key, $val);
    }

    /**
     * Delegate the magic method __cal() to the java object
     * to access the Java object method (and not the PHP
     * remote proxied object).
     *
     * @throws \Exception Depending on ThrowExceptionProxy
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__call($method, $args);
    }

    /**
     * @param mixed|null $args arguments
     *
     * @return ObjectIterator
     */
    public function getIterator(...$args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        if (empty($args)) {
            return $this->__delegate->getIterator();
        }

        return $this->__call('getIterator', $args);
    }

    /**
     * @param string|int $idx
     * @param mixed|null ...$args
     *
     * @return bool
     */
    public function offsetExists($idx, ...$args): bool
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (empty($args)) {
            return $this->__delegate->offsetExists($idx);
        }

        // In case we supplied more arguments than what ArrayAccess
        // suggest, let's try for a java method called offsetExists
        // with all the provided parameters
        array_unshift($args, $idx); // Will add idx at the beginning of args params
        return $this->__call('offsetExists', $args);
    }

    /**
     * @param string|int $idx
     * @param mixed|null ...$args additional arguments
     *
     * @return mixed
     */
    public function offsetGet($idx, ...$args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (empty($args)) {
            return $this->__delegate->offsetGet($idx);
        }
        array_unshift($args, $idx);

        return $this->__call('offsetGet', $args);
    }

    /**
     * @param string|int $idx
     * @param mixed      $val
     * @param mixed|null ...$args additional arguments
     *
     * @return mixed
     */
    public function offsetSet($idx, $val, ...$args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (count($args) == 0) {
            return $this->__delegate->offsetSet($idx, $val);
        }

        array_unshift($args, $idx, $val);

        return $this->__call('offsetSet', $args);
    }

    /**
     * @param mixed      $idx
     * @param mixed|null ...$args additional arguments
     *
     * @return mixed|void
     */
    public function offsetUnset($idx, ...$args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (count($args) == 0) {
            return $this->__delegate->offsetUnset($idx);
        }
        array_unshift($args, $idx);

        return $this->__call('offsetUnset', $args);
    }

    /**
     * @return int
     */
    public function get__java(): int
    {
        return $this->__java;
    }

    /**
     * Return java object id.
     *
     * @return int
     */
    public function __getJavaInternalObjectId(): int
    {
        return $this->__java;
    }

    /**
     * @return string
     */
    public function get__signature(): ?string
    {
        return $this->__signature;
    }

    /**
     * The PHP magic method __toString() cannot be applied
     * on the PHP object but has to be delegated to the Java one.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__toString();
    }

    /**
     * Returns the runtime class of this Object.
     * The returned Class object is the object that is locked by static synchronized methods of the represented class.
     *
     * @return Interfaces\JavaObject Java('java.lang.Object')
     */
    public function getClass(): Interfaces\JavaObject
    {
        return $this->__delegate->getClass();
    }
}

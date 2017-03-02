<?php
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
     * @var JavaProxy
     */
    protected $__delegate;
    protected $__serialID;
    protected $__factory;

    /**
     * @var int
     */
    protected $__java;
    protected $__signature;
    protected $__cancelProxyCreationTag;

    protected function __createDelegate()
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
    public function __cast($type)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__cast($type);
    }

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
        $this->__java = $this->__delegate->__java;
        $this->__client = $this->__delegate->__client;
    }

    public function __get($key)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__get($key);
    }

    public function __set($key, $val)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        $this->__delegate->__set($key, $val);
    }

    public function __call($method, $args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        return $this->__delegate->__call($method, $args);
    }

    /**
     * @return ObjectIterator
     */
    public function getIterator(...$args)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }

        if (count($args) == 0) {
            return $this->__delegate->getIterator();
        }

        return $this->__call('getIterator', $args);
    }

    /**
     * @param string|int $idx
     *
     * @return bool
     */
    public function offsetExists($idx)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (func_num_args() == 1) {
            return $this->__delegate->offsetExists($idx);
        }
        $args = func_get_args();

        return $this->__call('offsetExists', $args);
    }

    /**
     * @param string|int $idx
     *
     * @return mixed
     */
    public function offsetGet($idx)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (func_num_args() == 1) {
            return $this->__delegate->offsetGet($idx);
        }
        $args = func_get_args();

        return $this->__call('offsetGet', $args);
    }

    /**
     * @param string|int $idx
     * @param mixed      $val
     *
     * @return mixed
     */
    public function offsetSet($idx, $val)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (func_num_args() == 2) {
            return $this->__delegate->offsetSet($idx, $val);
        }
        $args = func_get_args();

        return $this->__call('offsetSet', $args);
    }

    public function offsetUnset($idx)
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (func_num_args() == 1) {
            return $this->__delegate->offsetUnset($idx);
        }
        $args = func_get_args();

        return $this->__call('offsetUnset', $args);
    }

    /**
     * @return int
     */
    public function get__java()
    {
        return $this->__java;
    }

    /**
     * Return java object id.
     *
     * @return int
     */
    public function __getJavaInternalObjectId()
    {
        return $this->__java;
    }

    /**
     * @return string
     */
    public function get__signature()
    {
        return $this->__signature;
    }

    /**
     * @return string
     */
    public function __toString()
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
     * @return Interfaces\JavaObject Java(java.lang.Object)
     */
    public function getClass()
    {
        return $this->__delegate->getClass();
    }
}

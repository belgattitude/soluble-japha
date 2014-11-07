<?php
/**
 * Soluble Japha / PhpJavaBridge
 *
 * Refactored version of phpjababridge's Java.inc file compatible
 * with php java bridge 6.2.1
 *
 *
 * @credits   http://php-java-bridge.sourceforge.net/pjb/
 *
 * @link      http://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2014 Soluble components
 * @author Vanvelthem Sébastien
 * @license   MIT
 *
 * The MIT License (MIT)
 * Copyright (c) 2014 Vanvelthem Sébastien
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
 */
namespace Soluble\Japha\Bridge\Driver\Pjb621;

abstract class AbstractJava implements \IteratorAggregate, \ArrayAccess, JavaType
{
    /**
     *
     * @var Client
     */
    public $__client;
    
    /**
     *
     * @var AbstractJava
     */
    public $__delegate;
    public $__serialID;
    public $__factory;
    
    /**
     *
     * @var int
     */
    public $__java;
    public $__signature;
    public $__cancelProxyCreationTag;

    
    public function __createDelegate()
    {
        $proxy = $this->__delegate = $this->__factory->create($this->__java, $this->__signature);
        $this->__java = $proxy->__java;
        $this->__signature = $proxy->__signature;
    }

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
        return array("__delegate");
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
     * 
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
     * 
     * @return ObjectIterator
     */
    public function getIterator()
    {
        if (!isset($this->__delegate)) {
            $this->__createDelegate();
        }
        if (func_num_args() == 0) {
            return $this->__delegate->getIterator();
        }
        $args = func_get_args();
        return $this->__call("getIterator", $args);
    }

    /**
     * 
     * @param string|integer $idx
     * @return boolean
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
        return $this->__call("offsetExists", $args);
    }

    /**
     * 
     * @param string|integer $idx
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
        return $this->__call("offsetGet", $args);
    }

    /**
     * 
     * @param string|integer $idx
     * @param mixed $val
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
        return $this->__call("offsetSet", $args);
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
        return $this->__call("offsetUnset", $args);
    }
    
    /**
     * @return integer
     */
    function get__java()
    {
        return $this->__java;
    }
    
    /**
     * @return string
     */
    function get__signature()
    {
        return $this->__signature;
    }
}

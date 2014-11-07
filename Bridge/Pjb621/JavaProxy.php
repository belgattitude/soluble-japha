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
namespace Soluble\Japha\Bridge\Pjb621;

class JavaProxy implements JavaType
{
    public $__serialID;
    /**
     *
     * @var int
     */
    public $__java;
    public $__signature;
    /**
     *
     * @var Client
     */
    public $__client;
    public $__tempGlobalRef;

    /**
     * 
     * @param int $java
     * @param string $signature
     */
    public function __construct($java, $signature)
    {
        $this->__java = $java;
        $this->__signature = $signature;
        $this->__client = __javaproxy_Client_getClient();
    }

    public function __cast($type)
    {
        return $this->__client->cast($this, $type);
    }

    public function __sleep()
    {
        $args = array($this, java_get_lifetime());
        $this->__serialID = $this->__client->invokeMethod(0, "serialize", $args);
        $this->__tempGlobalRef = $this->__client->globalRef;
        return array("__serialID", "__tempGlobalRef");
    }

    public function __wakeup()
    {
        $args = array($this->__serialID, java_get_lifetime());
        $this->__client = __javaproxy_Client_getClient();
        if ($this->__tempGlobalRef) {
            $this->__client->globalRef = $this->__tempGlobalRef;
        }
        $this->__tempGlobalRef = null;
        $this->__java = $this->__client->invokeMethod(0, "deserialize", $args);
    }

    public function __destruct()
    {
        if (isset($this->__client)) {
            $this->__client->unref($this->__java);
        }
    }

    public function __get($key)
    {
        return $this->__client->getProperty($this->__java, $key);
    }

    public function __set($key, $val)
    {
        $this->__client->setProperty($this->__java, $key, $val);
    }

    public function __call($method, $args)
    {
        return $this->__client->invokeMethod($this->__java, $method, $args);
    }

    public function __toString()
    {
        try {
            return $this->__client->invokeMethod(0, "ObjectToString", array($this));
        } catch (Exception\JavaException $ex) {
            trigger_error("Exception in Java::__toString(): " . java_truncate((string) $ex), E_USER_WARNING);
            return "";
        }
    }
    
    /**
     * @return int
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

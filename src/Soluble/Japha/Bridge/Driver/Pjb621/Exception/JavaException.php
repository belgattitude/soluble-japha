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
namespace Soluble\Japha\Bridge\Driver\Pjb621\Exception;

use Exception;
use Soluble\Japha\Bridge\Exception\JavaExceptionInterface;
use Soluble\Japha\Bridge\Driver\Pjb621\JavaType;

class JavaException extends Exception implements JavaType, JavaExceptionInterface
{
    public $__serialID;
    public $__java;
    
    /**
     *
     * @var Client
     */
    public $__client;
    public $__delegate;
    public $__signature;
    public $__hasDeclaredExceptions;

    public function __construct()
    {
        $this->__client = __javaproxy_Client_getClient();
        $args = func_get_args();
        $name = array_shift($args);
        if (is_array($name)) {
            $args = $name;
            $name = array_shift($args);
        }
        
        if (count($args) == 0) {
            parent::__construct($name);
        } else {
            parent::__construct($args[0]);
        }
        
        $delegate = $this->__delegate = $this->__client->createObject($name, $args);
        $this->__java = $delegate->__java;
        $this->__signature = $delegate->__signature;
        $this->__hasDeclaredExceptions = 'T';
    }

    public function __cast($type)
    {
        return $this->__delegate->__cast($type);
    }

    public function __sleep()
    {
        $this->__delegate->__sleep();
        return array("__delegate");
    }

    public function __wakeup()
    {
        $this->__delegate->__wakeup();
        $this->__java = $this->__delegate->__java;
        $this->__client = $this->__delegate->__client;
    }

    public function __get($key)
    {
        return $this->__delegate->__get($key);
    }

    public function __set($key, $val)
    {
        $this->__delegate->__set($key, $val);
    }

    public function __call($method, $args)
    {
        return $this->__delegate->__call($method, $args);
    }

    public function __toString()
    {
        return $this->__delegate->__toExceptionString($this->getTraceAsString());
    }
    
    /**
     * @return integer
     */
    public function get__java()
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
}

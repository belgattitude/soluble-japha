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
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62\Exception;

use Exception;
use Soluble\Japha\Bridge\Driver\Pjb62\JavaType;
use Soluble\Japha\Bridge\Driver\Pjb62\Client;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;
use Soluble\Japha\Interfaces\JavaObject;

/**
 * @method JavaObject getCause() Standard java Throwable::getCause() method: java('java.lang.String')
 */
class JavaException extends Exception implements JavaType
{
    public $__serialID;
    public $__java;

    /**
     * @var Client
     */
    public $__client;
    public $__delegate;
    public $__signature;
    public $__hasDeclaredExceptions;

    /**
     * JavaException constructor.
     *
     * @param mixed|null ...$args arguments
     */
    public function __construct(...$args)
    {
        $this->__client = PjbProxyClient::getInstance()->getClient();
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

        $this->__delegate = $this->__client->createObject($name, $args);
        $this->__java = $this->__delegate->get__java();
        $this->__signature = $this->__delegate->get__signature();
        $this->__hasDeclaredExceptions = 'T';
    }

    public function __cast($type)
    {
        return $this->__delegate->__cast($type);
    }

    public function __sleep()
    {
        $this->__delegate->__sleep();

        return ['__delegate'];
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->__delegate->__toExceptionString($this->getTraceAsString());
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
     * @return string|null
     */
    public function get__signature()
    {
        return $this->__signature;
    }
}

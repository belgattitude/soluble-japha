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
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Driver\Pjb62\Utils\HelperFunctions;

/**
 * Some annotations for java.lang.Object.
 *
 * @see http://docs.oracle.com/javase/7/docs/api/java/lang/Object.html
 *
 * @method JavaClass getClass() return object class name
 * @method bool equals(JavaObject $object)
 * @method string toString()
 * @method void offsetUnset($key)
 * @method void offsetSet($key, $value)
 * @method mixed offsetGet($key)
 * @method bool offsetExists($key)
 * @method mixed getIterator()
 * @method string getName()
 */
class JavaProxy implements JavaType
{
    protected $__serialID;

    /**
     * @var int
     */
    public $__java;

    /**
     * @var string
     */
    public $__signature;

    /**
     * @var \Soluble\Japha\Bridge\Driver\Pjb62\Client
     */
    public $__client;

    /**
     * @var GlobalRef|null
     */
    public $__tempGlobalRef;

    /**
     * @param int    $java
     * @param string $signature
     */
    public function __construct($java, $signature)
    {
        $this->__java = $java;
        $this->__signature = $signature;
        $this->__client = PjbProxyClient::getInstance()->getClient();
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function __cast(string $type)
    {
        return $this->__client->cast($this, $type);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $args = [$this, HelperFunctions::java_get_session_lifetime()];
        $this->__serialID = $this->__client->invokeMethod(0, 'serialize', $args);
        $this->__tempGlobalRef = $this->__client->globalRef;

        return ['__serialID', '__tempGlobalRef'];
    }

    public function __wakeup()
    {
        $args = [$this->__serialID, HelperFunctions::java_get_session_lifetime()];
        $this->__client = PjbProxyClient::getInstance()->getClient();
        if ($this->__tempGlobalRef) {
            $this->__client->globalRef = $this->__tempGlobalRef;
        }
        $this->__tempGlobalRef = null;
        $this->__java = $this->__client->invokeMethod(0, 'deserialize', $args);
    }

    /**
     * Automatically detroy this object
     * by delegating the unref to the bridge side.
     */
    public function __destruct()
    {
        if (isset($this->__client)) {
            $this->__client->unref($this->__java);
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->__client->getProperty($this->__java, $key);
    }

    /**
     * @param string $key
     * @param mixed  $val
     */
    public function __set(string $key, $val): void
    {
        $this->__client->setProperty($this->__java, $key, $val);
    }

    public function __call(string $method, array $args)
    {
        return $this->__client->invokeMethod($this->__java, $method, $args);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            return (string) $this->__client->invokeMethod(0, 'ObjectToString', [$this]);
        } catch (Exception\JavaException $ex) {
            $msg = 'Exception in Java::__toString(): ' . HelperFunctions::java_truncate((string) $ex);
            $this->__client->getLogger()->warning("[soluble-japha] $msg (" . __METHOD__ . ')');
            trigger_error($msg, E_USER_WARNING);

            return '';
        }
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
}

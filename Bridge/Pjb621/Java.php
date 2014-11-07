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
 * 
 * @method string getBufferContents()
 */

namespace Soluble\Japha\Bridge\Pjb621;

class Java extends AbstractJava
{

    //public function __construct($java_fqdn, $java_args=null)
    public function __construct()
    {
        $client = $this->__client = __javaproxy_Client_getClient();
        $args = func_get_args();


        $name = array_shift($args);
        if (is_array($name)) {
            $args = $name;
            $name = array_shift($args);
        }



        $sig = "&{$this->__signature}@{$name}";
        $len = count($args);
        $args2 = array();
        for ($i = 0; $i < $len; $i++) {
            $val = $args[$i];
            switch (gettype($val)) {
                case 'boolean': array_push($args2, $val);
                    $sig.='@b';
                    break;
                case 'integer': array_push($args2, $val);
                    $sig.='@i';
                    break;
                case 'double': array_push($args2, $val);
                    $sig.='@d';
                    break;
                case 'string': array_push($args2, htmlspecialchars($val, ENT_COMPAT));
                    $sig.='@s';
                    break;
                case 'array':$sig = "~INVALID";
                    break;
                case 'object':
                    if ($val instanceof JavaType) {
                        array_push($args2, $val->get__java());
                        $sig.="@o{$val->get__signature()}";
                    } else {
                        $sig = "~INVALID";
                    }
                    break;
                case 'resource': array_push($args2, $val);
                    $sig.='@r';
                    break;
                case 'NULL': array_push($args2, $val);
                    $sig.='@N';
                    break;
                case 'unknown type': array_push($args2, $val);
                    $sig.='@u';
                    break;
                default:
                    throw new Exception\IllegalArgumentException($val);
            }
        }
        
        if (array_key_exists($sig, $client->methodCache)) {
            $cacheEntry = &$client->methodCache[$sig];
            $client->sendBuffer.= $client->preparedToSendBuffer;
            if (strlen($client->sendBuffer) >= JAVA_SEND_SIZE) {
                if ($client->protocol->handler->write($client->sendBuffer) <= 0) {
                    throw new Exception\IllegalStateException("Connection out of sync,check backend log for details.");
                }
                $client->sendBuffer = null;
            }
            $client->preparedToSendBuffer = vsprintf($cacheEntry->fmt, $args2);
            $this->__java = ++$client->asyncCtx;

            $this->__factory = $cacheEntry->factory;
            $this->__signature = $cacheEntry->signature;
            $this->__cancelProxyCreationTag = ++$client->cancelProxyCreationTag;
        } else {
            
            $client->currentCacheKey = $sig;
            $this->__delegate = $client->createObject($name, $args);
            $delegate = $this->__delegate;
            
            $this->__java = $delegate->__java;
            $this->__signature = $delegate->__signature;
        }
    }

    public function __destruct()
    {
        if (!isset($this->__client)) {
            return;
        }
        $client = $this->__client;
        $preparedToSendBuffer = &$client->preparedToSendBuffer;
        if ($preparedToSendBuffer &&
                $client->cancelProxyCreationTag == $this->__cancelProxyCreationTag) {
            $preparedToSendBuffer[6] = "3";
            $client->sendBuffer.=$preparedToSendBuffer;
            $preparedToSendBuffer = null;
            $client->asyncCtx -=1;
        } else {
            if (!isset($this->__delegate)) {
                $client->unref($this->__java);
            }
        }
    }

    public function __call($method, $args)
    {
        $client = $this->__client;
        $sig = "@{$this->__signature}@$method";
        $len = count($args);
        $args2 = array($this->__java);
        for ($i = 0; $i < $len; $i++) {
            switch (gettype($val = $args[$i])) {
                case 'boolean': array_push($args2, $val);
                    $sig.='@b';
                    break;
                case 'integer': array_push($args2, $val);
                    $sig.='@i';
                    break;
                case 'double': array_push($args2, $val);
                    $sig.='@d';
                    break;
                case 'string': array_push($args2, htmlspecialchars($val, ENT_COMPAT));
                    $sig.='@s';
                    break;
                case 'array':$sig = "~INVALID";
                    break;
                case 'object':
                    if ($val instanceof JavaType) {
                        array_push($args2, $val->get__java());
                        $sig.="@o{$val->get__signature()}";
                    } else {
                        $sig = "~INVALID";
                    }
                    break;
                case 'resource': array_push($args2, $val);
                    $sig.='@r';
                    break;
                case 'NULL': array_push($args2, $val);
                    $sig.='@N';
                    break;
                case 'unknown type': array_push($args2, $val);
                    $sig.='@u';
                    break;
                default:
                    throw new Exception\IllegalArgumentException($val);
            }
        }
        if (array_key_exists($sig, $client->methodCache)) {
            $cacheEntry = &$client->methodCache[$sig];
            $client->sendBuffer.=$client->preparedToSendBuffer;
            if (strlen($client->sendBuffer) >= JAVA_SEND_SIZE) {
                if ($client->protocol->handler->write($client->sendBuffer) <= 0) {
                    throw new Exception\IllegalStateException("Out of sync. Check backend log for details.");
                }
                $client->sendBuffer = null;
            }
            $client->preparedToSendBuffer = vsprintf($cacheEntry->fmt, $args2);
            if ($cacheEntry->resultVoid) {
                $client->cancelProxyCreationTag +=1;
                return null;
            } else {
                $result = clone($client->cachedJavaPrototype);
                $result->__factory = $cacheEntry->factory;
                $result->__java = ++$client->asyncCtx;
                $result->__signature = $cacheEntry->signature;
                $result->__cancelProxyCreationTag = ++$client->cancelProxyCreationTag;
                return $result;
            }
        } else {
            $client->currentCacheKey = $sig;
            $retval = parent::__call($method, $args);
            return $retval;
        }
    }

}

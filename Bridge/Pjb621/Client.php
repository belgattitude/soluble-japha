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

class Client
{
    public $RUNTIME;
    public $result;
    public $exception;
    public $parser;
    public $simpleArg;
    
    /**
     *
     * @var CompositeArg
     */
    public $compositeArg;
    
    /**
     *
     * @var SimpleFactory
     */
    public $simpleFactory;
    
    /**
     *
     * @var ProxyFactory
     */
    public $proxyFactory;
    
    /**
     *
     * @var IteratorProxyFactory
     */
    public $iteratorProxyFactory;
    
    /**
     *
     * @var ArrayProxyFactory
     */
    public $arrayProxyFactory;
    
    /**
     *
     * @var ExceptionProxyFactory
     */
    public $exceptionProxyFactory;
    
    /**
     *
     * @var ThrowExceptionProxyFactory
     */
    public $throwExceptionProxyFactory;
    
    /**
     *
     * @var Arg
     */
    public $arg;
    public $asyncCtx;
    public $cancelProxyCreationCounter;
    
    /**
     *
     * @var GlobalRef
     */
    public $globalRef;
    public $stack;
    public $defaultCache = array();
    public $asyncCache = array();
    public $methodCache;
    public $isAsync = 0;
    public $currentCacheKey;
    public $currentArgumentsFormat;
    public $cachedJavaPrototype;
    public $sendBuffer;
    public $preparedToSendBuffer;
    public $inArgs;
    
    /**
     *
     * @var Protocol
     */
    public $protocol;
    

    
    public function __construct()
    {
        $this->RUNTIME = array();
        $this->RUNTIME["NOTICE"] = '***USE echo java_inspect(jVal) OR print_r(java_values(jVal)) TO SEE THE CONTENTS OF THIS JAVA OBJECT!***';
        $this->parser = new Parser($this);
        $this->protocol = new Protocol($this);
        $this->simpleFactory = new SimpleFactory($this);
        $this->proxyFactory = new ProxyFactory($this);
        $this->arrayProxyFactory = new ArrayProxyFactory($this);
        $this->iteratorProxyFactory = new IteratorProxyFactory($this);
        $this->exceptionProxyFactory = new ExceptionProxyFactory($this);
        $this->throwExceptionProxyFactory = new ThrowExceptionProxyFactory($this);
        $this->cachedJavaPrototype = new JavaProxyProxy($this);
        $this->simpleArg = new Arg($this);
        $this->globalRef = new GlobalRef();
        $this->asyncCtx = $this->cancelProxyCreationCounter = 0;
        $this->methodCache = $this->defaultCache;
        $this->inArgs = false;
    }

    public function read($size)
    {
        return $this->protocol->read($size);
    }

    public function setDefaultHandler()
    {
        $this->methodCache = $this->defaultCache;
    }

    public function setAsyncHandler()
    {
        $this->methodCache = $this->asyncCache;
    }

    public function handleRequests()
    {
        $tail_call = false;
        do {
            $this->stack = array($this->arg = $this->simpleArg);
            $this->idx = 0;
            $this->parser->parse();
            if ((count($this->stack)) > 1) {
                $arg = array_pop($this->stack);
                $this->apply($arg);
                $tail_call = true;
            } else {
                $tail_call = false;
            }
            $this->stack = null;
        } while ($tail_call);
        return 1;
    }

    public function getWrappedResult($wrap)
    {
        return $this->simpleArg->getResult($wrap);
    }

    public function getInternalResult()
    {
        return $this->getWrappedResult(false);
    }

    public function getResult()
    {
        return $this->getWrappedResult(true);
    }

    /**
     * 
     * @param array $type
     * @return SimpleFactory
     */
    public function getProxyFactory($type)
    {
        switch ($type[0]) {
            case 'E':
                $factory = $this->exceptionProxyFactory;
                break;
            case 'C':
                $factory = $this->iteratorProxyFactory;
                break;
            case 'A':
                $factory = $this->arrayProxyFactory;
                break;
            default:
            case 'O':
                $factory = $this->proxyFactory;
        }
        return $factory;
    }

    public function link(&$arg, &$newArg)
    {
        $arg->linkResult($newArg->val);
        $newArg->parentArg = $arg;
    }

    public function getExact($str)
    {
        return hexdec($str);
    }

    public function getInexact($str)
    {
        $val = null;
        sscanf($str, "%e", $val);
        return $val;
    }

    /**
     * 
     * @param array $name
     * @param array $st
     */
    public function begin($name, $st)
    {
        $arg = $this->arg;
        switch ($name[0]) {
            case 'A':
                $object = $this->globalRef->get($this->getExact($st['v']));
                $newArg = new ApplyArg($this, 'A', $this->parser->getData($st['m']), $this->parser->getData($st['p']), $object, $this->getExact($st['n']));
                $this->link($arg, $newArg);
                array_push($this->stack, $this->arg = $newArg);
                break;
            case 'X':
                $newArg = new CompositeArg($this, $st['t']);
                $this->link($arg, $newArg);
                array_push($this->stack, $this->arg = $newArg);
                break;
            case 'P':
                if ($arg->type == 'H') {
                    $s = $st['t'];
                    if ($s[0] == 'N') {
                        $arg->setIndex($this->getExact($st['v']));
                    } else {
                        $arg->setIndex($this->parser->getData($st['v']));
                    }
                } else {
                    $arg->setNextIndex();
                }
                break;
            case 'S':
                $arg->setResult($this->parser->getData($st['v']));
                break;
            case 'B':
                $s = $st['v'];
                $arg->setResult($s[0] == 'T');
                break;
            case 'L':
                $sign = $st['p'];
                $val = $this->getExact($st['v']);
                if ($sign[0] == 'A') {
                    $val*=-1;
                }
                $arg->setResult($val);
                break;
            case 'D':
                $arg->setResult($this->getInexact($st['v']));
                break;
            case 'V':
                if ($st['n'] != 'T') {
                    $arg->setVoidSignature();
                }
                case 'N':
                    $arg->setResult(null);
                break;
            case 'F':
                break;
            case 'O':
                $arg->setFactory($this->getProxyFactory($st['p']));
                $arg->setResult($this->asyncCtx = $this->getExact($st['v']));
                if ($st['n'] != 'T') {
                    $arg->setSignature($st['m']);
                }
                break;
            case 'E':
                $arg->setFactory($this->throwExceptionProxyFactory);
                $arg->setException($st['m']);
                $arg->setResult($this->asyncCtx = $this->getExact($st['v']));
                break;
            default:
                $this->parser->parserError();
        }
    }

    /**
     * 
     * @param array $name
     */
    public function end($name)
    {
        switch ($name[0]) {
            case 'X':
                $frame = array_pop($this->stack);
                $this->arg = $frame->parentArg;
                break;
        }
    }

    /**
     * 
     * @return ParserString
     */
    public function createParserString()
    {
        return new ParserString();
    }

    public function writeArg($arg)
    {
        if (is_string($arg)) {
            $this->protocol->writeString($arg);
        } elseif (is_object($arg)) {
            if ((!$arg instanceof JavaType)) {
                error_log((string) new IllegalArgumentException($arg));
                trigger_error("argument '" . get_class($arg) . "' is not a Java object,using NULL instead", E_USER_WARNING);
                $this->protocol->writeObject(null);
            } else {
                $this->protocol->writeObject($arg->__java);
            }
        } elseif (is_null($arg)) {
            $this->protocol->writeObject(null);
        } elseif (is_bool($arg)) {
            $this->protocol->writeBoolean($arg);
        } elseif (is_integer($arg)) {
            $this->protocol->writeLong($arg);
        } elseif (is_float($arg)) {
            $this->protocol->writeDouble($arg);
        } elseif (is_array($arg)) {
            $wrote_begin = false;
            foreach ($arg as $key => $val) {
                if (is_string($key)) {
                    if (!$wrote_begin) {
                        $wrote_begin = 1;
                        $this->protocol->writeCompositeBegin_h();
                    }
                    $this->protocol->writePairBegin_s($key);
                    $this->writeArg($val);
                    $this->protocol->writePairEnd();
                } else {
                    if (!$wrote_begin) {
                        $wrote_begin = 1;
                        $this->protocol->writeCompositeBegin_h();
                    }
                    $this->protocol->writePairBegin_n($key);
                    $this->writeArg($val);
                    $this->protocol->writePairEnd();
                }
            }
            if (!$wrote_begin) {
                $this->protocol->writeCompositeBegin_a();
            }
            $this->protocol->writeCompositeEnd();
        }
    }

    public function writeArgs($args)
    {
        $this->inArgs = true;
        $n = count($args);
        for ($i = 0; $i < $n; $i++) {
            $this->writeArg($args[$i]);
        }
        $this->inArgs = false;
    }

    public function createObject($name, $args)
    {
        $this->protocol->createObjectBegin($name);
        $this->writeArgs($args);
        $this->protocol->createObjectEnd();
        $val = $this->getInternalResult();
        return $val;
    }

    public function referenceObject($name, $args)
    {
        $this->protocol->referenceBegin($name);
        $this->writeArgs($args);
        $this->protocol->referenceEnd();
        $val = $this->getInternalResult();
        return $val;
    }

    public function getProperty($object, $property)
    {
        $this->protocol->propertyAccessBegin($object, $property);
        $this->protocol->propertyAccessEnd();
        return $this->getResult();
    }

    public function setProperty($object, $property, $arg)
    {
        $this->protocol->propertyAccessBegin($object, $property);
        $this->writeArg($arg);
        $this->protocol->propertyAccessEnd();
        $this->getResult();
    }

    public function invokeMethod($object, $method, $args)
    {
        $this->protocol->invokeBegin($object, $method);
        $this->writeArgs($args);
        $this->protocol->invokeEnd();
        $val = $this->getResult();
        return $val;
    }

    public function unref($object)
    {
        if (isset($this->protocol)) {
            $this->protocol->writeUnref($object);
        }
    }

    /**
     * 
     * @param Arg $arg
     * @throws JavaException
     */
    public function apply($arg)
    {
        $name = $arg->p;
        $object = $arg->v;
        $ob = ($object == null) ? $name : array(&$object, $name);
        $isAsync = $this->isAsync;
        $methodCache = $this->methodCache;
        $currentArgumentsFormat = $this->currentArgumentsFormat;
        try {
            $res = $arg->getResult(true);
            if ((($object == null) && !function_exists($name)) || (!($object == null) && !method_exists($object, $name))) {
                throw new Exception\JavaException("java.lang.NoSuchMethodError", "$name");
            }
            $res = call_user_func_array($ob, $res);
            if (is_object($res) && (!($res instanceof JavaType))) {
                trigger_error("object returned from $name() is not a Java object", E_USER_WARNING);
                $this->protocol->invokeBegin(0, "makeClosure");
                $this->protocol->writeULong($this->globalRef->add($res));
                $this->protocol->invokeEnd();
                $res = $this->getResult();
            }
            $this->protocol->resultBegin();
            $this->writeArg($res);
            $this->protocol->resultEnd();
        } catch (Exception\JavaException $e) {
            $trace = $e->getTraceAsString();
            $this->protocol->resultBegin();
            $this->protocol->writeException($e->__java, $trace);
            $this->protocol->resultEnd();
        } catch (\Exception $ex) {
            error_log($ex->__toString());
            trigger_error("Unchecked exception detected in callback", E_USER_ERROR);
            die(1);
        }
        $this->isAsync = $isAsync;
        $this->methodCache = $methodCache;
        $this->currentArgumentsFormat = $currentArgumentsFormat;
    }

    /**
     * 
     * @param mixed $object
     * @param array $type
     * @return mixed
     * @throws Exception\RuntimeException
     */
    public function cast($object, $type)
    {
        switch ($type[0]) {
            case 'S': case 's':
                return $this->invokeMethod(0, "castToString", array($object));
            case 'B': case 'b':
                return $this->invokeMethod(0, "castToBoolean", array($object));
            case 'L': case 'I': case 'l': case 'i':
                return $this->invokeMethod(0, "castToExact", array($object));
            case 'D': case 'd': case 'F': case 'f':
                return $this->invokeMethod(0, "castToInExact", array($object));
            case 'N': case 'n':
                return null;
            case 'A': case 'a':
                return $this->invokeMethod(0, "castToArray", array($object));
            case 'O': case 'o':
                return $object;
            default:
                throw new Exception\RuntimeException("$type illegal");
        }
    }

    public function getContext()
    {
        static $cache = null;
        if (!is_null($cache)) {
            return $cache;
        }
        return $cache = $this->invokeMethod(0, "getContext", array());
    }

    public function getSession($args)
    {
        return $this->invokeMethod(0, "getSession", $args);
    }

    public function getServerName()
    {
        static $cache = null;
        if (!is_null($cache)) {
            return $cache;
        }
        return $cache = $this->protocol->getServerName();
    }
}

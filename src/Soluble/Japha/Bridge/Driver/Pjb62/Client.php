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

use ArrayObject;
use Psr\Log\LoggerInterface;
use Soluble\Japha\Bridge\Exception\JavaException;
use Soluble\Japha\Interfaces;
use Soluble\Japha\Bridge\Driver\Pjb62\Utils\HelperFunctions;

class Client
{
    /**
     * @var array
     */
    public $RUNTIME;
    public $result;
    public $exception;
    /**
     * @var ParserFactory
     */
    public $parser;

    /**
     * @var Arg|CompositeArg
     */
    public $simpleArg;

    /**
     * @var CompositeArg
     */
    public $compositeArg;

    /**
     * @var SimpleFactory
     */
    public $simpleFactory;

    /**
     * @var ProxyFactory
     */
    public $proxyFactory;

    /**
     * @var IteratorProxyFactory
     */
    public $iteratorProxyFactory;

    /**
     * @var ArrayProxyFactory
     */
    public $arrayProxyFactory;

    /**
     * @var ExceptionProxyFactory
     */
    public $exceptionProxyFactory;

    /**
     * @var ThrowExceptionProxyFactory
     */
    public $throwExceptionProxyFactory;

    /**
     * @var Arg|CompositeArg
     */
    public $arg;
    /**
     * @var int
     */
    public $asyncCtx;

    /**
     * @var int
     */
    public $cancelProxyCreationTag;

    /**
     * @var GlobalRef
     */
    public $globalRef;
    public $stack;
    /**
     * @var array
     */
    public $defaultCache = [];
    /**
     * @var array
     */
    public $asyncCache = [];
    /**
     * @var array
     */
    public $methodCache = [];
    public $isAsync = 0;
    /**
     * @var string|null
     */
    public $currentCacheKey;

    /**
     * @var string
     */
    public $currentArgumentsFormat;
    public $cachedJavaPrototype;

    /**
     * @var string|null
     */
    public $sendBuffer;
    /**
     * @var string|null
     */
    public $preparedToSendBuffer;
    public $inArgs;

    /**
     * @var int
     */
    protected $idx;

    /**
     * @var Protocol
     */
    public $protocol;

    /**
     * @var array
     */
    protected $cachedValues = [];

    /**
     * @var ArrayObject
     */
    protected $params;

    /**
     * @var string
     */
    public $java_servlet;

    /**
     * @var string
     */
    public $java_hosts;

    /**
     * @var int
     */
    public $java_recv_size;

    /**
     * @var int
     */
    public $java_send_size;

    /**
     * @var int
     */
    protected $default_buffer_size = 8192;

    /**
     * @var string
     */
    protected $internal_encoding = 'UTF-8';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ArrayObject $params
     */
    public function __construct(ArrayObject $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;

        if (array_key_exists('JAVA_SEND_SIZE', $params) && $params['JAVA_SEND_SIZE'] != 0) {
            $this->java_send_size = $params['JAVA_SEND_SIZE'];
        } else {
            $this->java_send_size = $this->default_buffer_size;
        }

        if (array_key_exists('JAVA_RECV_SIZE', $params) && $params['JAVA_RECV_SIZE'] != 0) {
            $this->java_recv_size = $params['JAVA_RECV_SIZE'];
        } else {
            $this->java_recv_size = $this->default_buffer_size;
        }

        if (isset($params['internal_encoding'])) {
            $this->internal_encoding = $params['internal_encoding'];
        }

        $this->java_hosts = $params['JAVA_HOSTS'];
        $this->java_servlet = $params['JAVA_SERVLET'];

        $this->RUNTIME = [];
        $this->RUNTIME['NOTICE'] = '***USE echo $adapter->getDriver()->inspect(jVal) OR print_r($adapter->values(jVal)) TO SEE THE CONTENTS OF THIS JAVA OBJECT!***';
        $this->parser = new ParserFactory($this, $params['XML_PARSER_FORCE_SIMPLE_PARSER'] ?? false);
        $this->protocol = new Protocol($this, $this->java_hosts, $this->java_servlet, $this->java_recv_size, $this->java_send_size);
        $this->simpleFactory = new SimpleFactory($this);
        $this->proxyFactory = new ProxyFactory($this);
        $this->arrayProxyFactory = new ArrayProxyFactory($this);
        $this->iteratorProxyFactory = new IteratorProxyFactory($this);
        $this->exceptionProxyFactory = new ExceptionProxyFactory($this);
        $this->throwExceptionProxyFactory = new ThrowExceptionProxyFactory($this);
        $this->cachedJavaPrototype = new JavaProxyProxy($this);
        $this->simpleArg = new Arg($this);
        $this->globalRef = new GlobalRef();
        $this->asyncCtx = $this->cancelProxyCreationTag = 0;
        $this->methodCache = $this->defaultCache;
        $this->inArgs = false;

        $this->cachedValues = [
            'getContext' => null,
            'getServerName' => null
        ];
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function read($size): string
    {
        $str = $this->protocol->read($size);
    }

    public function setDefaultHandler(): void
    {
        $this->methodCache = $this->defaultCache;
    }

    public function setAsyncHandler(): void
    {
        $this->methodCache = $this->asyncCache;
    }

    /**
     * Handle request.
     *
     * @throws Exception\RuntimeException
     */
    public function handleRequests(): void
    {
        $tail_call = false;
        do {
            $this->arg = $this->simpleArg;
            $this->stack = [$this->arg];
            $this->idx = 0;
            $this->parser->parse();
            if (count($this->stack) > 1) {
                $arg = array_pop($this->stack);
                if ($arg instanceof ApplyArg) {
                    $this->apply($arg);
                } else {
                    $msg = 'Error: $arg should be of type ApplyArg, error in client';
                    $this->logger->critical($msg);
                    throw new Exception\RuntimeException($msg);
                }
                $tail_call = true;
            }
            $this->stack = null;
        } while ($tail_call);
    }

    /**
     * @param bool $wrap
     */
    public function getWrappedResult($wrap)
    {
        return $this->simpleArg->getResult($wrap);
    }

    public function getInternalResult(): JavaProxy
    {
        return $this->getWrappedResult(false);
    }

    public function getResult()
    {
        return $this->getWrappedResult(true);
    }

    /**
     * @param string $type
     *
     * @return SimpleFactory
     */
    protected function getProxyFactory(string $type): SimpleFactory
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
            case 'O':
            default:
                $factory = $this->proxyFactory;
        }

        return $factory;
    }

    /**
     * @param Arg          $arg
     * @param CompositeArg $newArg
     */
    protected function link(&$arg, &$newArg): void
    {
        $arg->linkResult($newArg->val);
        $newArg->parentArg = $arg;
    }

    /**
     * @param string $str
     *
     * @return int
     */
    protected function getExact($str)
    {
        return hexdec($str);
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    protected function getInexact($str)
    {
        $val = null;
        sscanf($str, '%e', $val);

        return $val;
    }

    /**
     * @param string $name
     * @param array  $st   param
     */
    public function begin(string $name, array $st): void
    {
        $arg = $this->arg;
        $code = $name[0];
        switch ($code) {
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
                    $val *= -1;
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
                // possible bugfix, the break was missing here
                break;
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
                $this->protocol->handler->shutdownBrokenConnection(
                    sprintf(
                        'Parser error, check the backend for details, $name: %s, ' .
                        '$st params: %s',
                        $name,
                        json_encode($st)
                    )
                );
        }
    }

    /**
     * @param string $name
     */
    public function end(string $name): void
    {
        switch ($name[0]) {
            case 'X':
                $frame = array_pop($this->stack);
                $this->arg = $frame->parentArg;
                break;
        }
    }

    /**
     * @throws JavaException
     *
     * @param mixed $arg
     */
    protected function writeArg($arg): void
    {
        if (is_string($arg)) {
            $this->protocol->writeString($arg);
        } elseif (is_object($arg)) {
            if (!$arg instanceof JavaType) {
                $msg = "Client failed to writeArg(), IllegalArgument 'arg:" . get_class($arg) . "' not a Java object, using NULL instead";
                $this->logger->error("[soluble-japha] $msg (" . __METHOD__ . ')');
                //trigger_error($msg, E_USER_WARNING);
                $this->protocol->writeObject(null);
            } else {
                $this->protocol->writeObject($arg->get__java());
            }
        } elseif (null === $arg) {
            $this->protocol->writeObject(null);
        } elseif (is_bool($arg)) {
            $this->protocol->writeBoolean($arg);
        } elseif (is_int($arg)) {
            $this->protocol->writeLong($arg);
        } elseif (is_float($arg)) {
            $this->protocol->writeDouble($arg);
        } elseif (is_array($arg)) {
            $wrote_begin = false;
            foreach ($arg as $key => $val) {
                if (is_string($key)) {
                    if (!$wrote_begin) {
                        $wrote_begin = true;
                        $this->protocol->writeCompositeBegin_h();
                    }
                    $this->protocol->writePairBegin_s($key);
                    $this->writeArg($val);
                    $this->protocol->writePairEnd();
                } else {
                    if (!$wrote_begin) {
                        $wrote_begin = true;
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

    /**
     * @param array $args
     */
    protected function writeArgs(array $args): void
    {
        $this->inArgs = true;
        $n = count($args);
        for ($i = 0; $i < $n; ++$i) {
            $this->writeArg($args[$i]);
        }
        $this->inArgs = false;
    }

    /**
     * @param string $name java class name, i.e java.math.BigInteger
     * @param array  $args
     *
     * @return JavaProxy
     */
    public function createObject(string $name, array $args): JavaProxy
    {
        $this->protocol->createObjectBegin($name);
        $this->writeArgs($args);
        $this->protocol->createObjectEnd();
        $val = $this->getInternalResult();

        return $val;
    }

    /**
     * @param string $name java class name, i.e java.math.BigInteger
     * @param array  $args
     *
     * @return JavaProxy
     */
    public function referenceObject(string $name, array $args): JavaProxy
    {
        $this->protocol->referenceBegin($name);
        $this->writeArgs($args);
        $this->protocol->referenceEnd();
        $val = $this->getInternalResult();

        return $val;
    }

    /**
     * @param int    $object
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty(int $object, string $property)
    {
        $this->protocol->propertyAccessBegin($object, $property);
        $this->protocol->propertyAccessEnd();

        return $this->getResult();
    }

    /**
     * @param int    $object
     * @param string $property
     * @param mixed  $arg
     *
     * @return mixed
     */
    public function setProperty(int $object, string $property, $arg): void
    {
        $this->protocol->propertyAccessBegin($object, $property);
        $this->writeArg($arg);
        $this->protocol->propertyAccessEnd();
        $this->getResult();
    }

    /**
     * Invoke a method on java object.
     *
     * @param int    $object_id a java object or type
     * @param string $method    method name
     * @param array  $args      arguments to send with method
     *
     * @return mixed
     */
    public function invokeMethod(int $object_id, string $method, array $args = [])
    {
        $this->protocol->invokeBegin($object_id, $method);
        $this->writeArgs($args);

        $this->protocol->invokeEnd();
        $val = $this->getResult();

        return $val;
    }

    /**
     * Write exit code.
     *
     * @param int $code
     */
    public function setExitCode(int $code): void
    {
        if (isset($this->protocol)) {
            $this->protocol->writeExitCode($code);
        }
    }

    /**
     * Unref will be called whenever a JavaObject is not used,
     * see JavaProxy::__destruct() method.
     *
     * @param int $object object identifier
     */
    public function unref(?int $object): void
    {
        if (isset($this->protocol)) {
            $this->protocol->writeUnref($object);
        }
    }

    /**
     * @param ApplyArg $arg
     *
     * @throws Exception\JavaException
     * @throws Exception\RuntimeException
     */
    public function apply(ApplyArg $arg): void
    {
        $name = $arg->p;
        $object = $arg->v;
        $ob = ($object == null) ? $name : [&$object, $name];
        $isAsync = $this->isAsync;
        $methodCache = $this->methodCache;
        $currentArgumentsFormat = $this->currentArgumentsFormat;
        try {
            $res = $arg->getResult(true);
            if ((($object == null) && !function_exists($name)) || (!($object == null) && !method_exists($object, $name))) {
                throw new Exception\JavaException('java.lang.NoSuchMethodError', (string) $name);
            }
            $res = call_user_func_array($ob, $res);
            if (is_object($res) && (!($res instanceof JavaType))) {
                $msg = "Client failed to applyArg(), Object returned from '$name()' is not a Java object";
                $this->logger->warning("[soluble-japha] $msg (" . __METHOD__ . ')');
                trigger_error($msg, E_USER_WARNING);

                $this->protocol->invokeBegin(0, 'makeClosure');
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
        } catch (\Throwable $ex) {
            $msg = 'Unchecked exception detected in callback (' . $ex->__toString() . ')';
            $this->logger->error("[soluble-japha] $msg (" . __METHOD__ . ')');
            trigger_error($msg, E_USER_WARNING);
            $uncheckedException = new Exception\RuntimeException($msg);
            throw $uncheckedException;
        }
        $this->isAsync = $isAsync;
        $this->methodCache = $methodCache;
        $this->currentArgumentsFormat = $currentArgumentsFormat;
    }

    /**
     * Cast an object to a certain type.
     *
     * @param JavaProxy $object
     * @param string    $type
     *
     * @return mixed
     *
     * @throws Exception\RuntimeException
     */
    public function cast(JavaProxy $object, $type)
    {
        $code = strtoupper($type[0]);
        switch ($code) {
            case 'S':
                return $this->invokeMethod(0, 'castToString', [$object]);
            case 'B':
                return $this->invokeMethod(0, 'castToBoolean', [$object]);
            case 'L':
            case 'I':
                return $this->invokeMethod(0, 'castToExact', [$object]);
            case 'D':
            case 'F':
                return $this->invokeMethod(0, 'castToInExact', [$object]);
            case 'N':
                return null;
            case 'A':
                return $this->invokeMethod(0, 'castToArray', [$object]);
            case 'O':
                return $object;
            default:
                throw new Exception\RuntimeException("Illegal type '$code' for casting");
        }
    }

    /**
     * Returns the jsr223 script context handle.
     *
     * Exposes the bindings from the ENGINE_SCOPE to PHP scripts. Values
     * set with engine.set("key", val) can be fetched from PHP with
     * java_context()->get("key"). Values set with
     * java_context()->put("key", java_closure($val)) can be fetched from
     * Java with engine.get("key"). The get/put methods are convenience shortcuts for getAttribute/setAttribute. Example:
     * <code>
     * engine.put("key1", 2);
     * engine.eval("<?php java_context()->put("key2", 1+(int)(string)java_context()->get('key1'));?>");
     * System.out.println(engine.get("key2"));
     *</code>
     *
     * A synchronized init() procedure can be called from the context to initialize a library once, and a shutdown hook can be registered to destroy the library before the (web-) context is destroyed. The init hook can be written in PHP, but the shutdown hook must be written in Java. Example:
     * <code>
     * function getShutdownHook() { return java("myJavaHelper")->getShutdownHook(); }
     * function call() { // called by init()
     *   ...
     *   // register shutdown hook
     *   java_context()->onShutdown(getShutdownHook());
     * }
     * java_context()->init(java_closure(null, null, java("java.util.concurrent.Callable")));
     * </code>
     *
     * It is possible to access implicit web objects (the session, the
     * application store etc.) from the context. Example:
     * <code>
     * $req = $ctx->getHttpServletRequest();
     * $res = $ctx->getHttpServletResponse();
     * $servlet = $ctx->getServlet();
     * $config = $ctx->getServletConfig();
     * $context = $ctx->getServletContext();
     * </code>
     *
     * The global bindings (shared with all available script engines) are
     * available from the GLOBAL_SCOPE, the script engine bindings are
     * available from the ENGINE_SCOPE. Example
     *
     * <code>
     * define ("ENGINE_SCOPE", 100);
     * define ("GLOBAL_SCOPE", 200);
     * echo java_context()->getBindings(ENGINE_SCOPE)->keySet();
     * echo java_context()->getBindings(GLOBAL_SCOPE)->keySet();
     * </code>
     *
     * Furthermore the context exposes the java continuation to PHP scripts.
     * Example which closes over the current environment and passes it back to java:
     * <code>
     * define ("ENGINE_SCOPE", 100);
     * $ctx = java_context();
     * if(java_is_false($ctx->call(java_closure()))) die "Script should be called from java";
     * </code>
     *
     * A second example which shows how to invoke PHP methods without the JSR 223 getInterface() and invokeMethod()
     * helper procedures. The Java code can fetch the current PHP continuation from the context using the key "php.java.bridge.PhpProcedure":
     * <code>
     * String s = "<?php class Runnable { function run() {...} };
     *            // example which captures an environment and
     *            // passes it as a continuation back to Java
     *            $Runnable = java('java.lang.Runnable');
     *            java_context()->call(java_closure(new Runnable(), null, $Runnable));
     *            ?>";
     * ScriptEngine e = new ScriptEngineManager().getEngineByName("php-invocable");
     * e.eval (s);
     * Thread t = new Thread((Runnable)e.get("php.java.bridge.PhpProcedure"));
     * t.join ();
     * ((Closeable)e).close ();
     * </code>
     *
     * @return Interfaces\JavaObject
     */
    public function getContext(): Interfaces\JavaObject
    {
        if ($this->cachedValues['getContext'] === null) {
            $this->cachedValues['getContext'] = $this->invokeMethod(0, 'getContext', []);
        }

        return $this->cachedValues['getContext'];
    }

    /**
     * Return a java (servlet) session handle.
     *
     * When getJavaSession() is called without
     * arguments, the session is shared with java.
     * Example:
     * <code>
     * $driver->getJavaSession()->put("key", new Java("java.lang.Object"));
     * [...]
     * </code>
     * The java components (jsp, servlets) can retrieve the value, for
     * example with:
     * <code>
     * getSession().getAttribute("key");
     * </code>
     *
     * When java_session() is called with a session name, the session
     * is not shared with java and no cookies are set. Example:
     * <code>
     * $driver->getJavaSession("myPublicApplicationStore")->put("key", "value");
     * </code>
     *
     * When java_session() is called with a second argument set to true,
     * a new session is allocated, the old session is destroyed if necessary.
     * Example:
     * <code>
     * $driver->getJavaSession(null, true)->put("key", "val");
     * </code>
     *
     * The optional third argument specifies the default lifetime of the session, it defaults to <code> session.gc_maxlifetime </code>. The value 0 means that the session never times out.
     *
     * The synchronized init() and onShutdown() callbacks from
     * java_context() and the JPersistenceAdapter (see
     * JPersistenceAdapter.php from the php_java_lib directory) may also
     * be useful to load a Java singleton object after the JavaBridge
     * library has been initialized, and to store it right before the web
     * context or the entire JVM will be terminated.
     *
     * @param array $args
     *
     * @return Interfaces\JavaObject
     */
    public function getSession(array $args = []): Interfaces\JavaObject
    {
        if (!isset($args[0])) {
            $args[0] = null;
        }

        if (!isset($args[1])) {
            $args[1] = 0;
        } // ISession.SESSION_GET_OR_CREATE
        elseif ($args[1] === true) {
            $args[1] = 1;
        } // ISession.SESSION_CREATE_NEW
        else {
            $args[1] = 2;
        } // ISession.SESSION_GET

        if (!isset($args[2])) {
            $args[2] = HelperFunctions::java_get_session_lifetime();
        }

        return $this->invokeMethod(0, 'getSession', $args);
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        if ($this->cachedValues['getServerName'] === null) {
            $this->cachedValues['getServerName'] = $this->protocol->getServerName();
        }

        return $this->cachedValues['getServerName'];
    }

    /**
     * Return client parameters.
     *
     * @return ArrayObject
     */
    public function getParams(): \ArrayObject
    {
        return $this->params;
    }

    /**
     * Return client parameter by name.
     *
     * @param string $param
     *
     * @return string|int
     */
    public function getParam($param)
    {
        return $this->params[$param];
    }

    /**
     * @return string
     */
    public function getInternalEncoding(): string
    {
        return $this->internal_encoding;
    }
}

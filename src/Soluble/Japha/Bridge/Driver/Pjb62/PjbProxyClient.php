<?php

declare(strict_types=1);

/**
 * Soluble Japha / PhpJavaBridge.
 *
 * @author Vanvelthem Sébastien
 * @license MIT
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Exception;
use Soluble\Japha\Interfaces;
use Soluble\Japha\Bridge\Driver\ClientInterface;
use ArrayObject;
use Soluble\Japha\Bridge\Driver\Pjb62\Exception\IllegalArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PjbProxyClient implements ClientInterface
{
    /**
     * @var PjbProxyClient|null
     */
    protected static $instance;

    protected static $unregistering = false;

    /**
     * @var array
     */
    protected $defaultOptions = [
        'java_disable_autoload' => true,
        'java_log_level' => null,
        'java_send_size' => 8192,
        'java_recv_size' => 8192,
        // By default do not use persistent connection
        'use_persistent_connection' => false,
        // java_prefer_values=true is the default working mode
        // of the soluble-japha client... It may be less efficient,
        // because it casts java String, Boolean, Integer... objects
        // automatically into (string, bool, integer...), and thus
        // not require additional writing like ($ba->values($myInt)) in
        // order to use a remote object. But prevent to work on the proxy instead,
        // so the value is always transferred for those types. If you put
        // at false you'll have to rework on the code.
        'java_prefer_values' => true,
        // use SimpleParser (pure PHP code) even if NativeParser (based on xml_* php functions) may be used
        // should only be used to workaround bugs or limitations regarding the xml extension
        'force_simple_xml_parser' => false,
    ];

    /**
     * @var Client|null
     */
    protected static $client;

    /**
     * Internal cache for already loaded Java classes.
     *
     * @var array
     */
    protected $classMapCache = [];

    /**
     * @var string
     */
    protected $compatibilityOption;

    /**
     * @var ArrayObject
     */
    protected $options;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string|null
     */
    protected static $instanceOptionsKey;

    /**
     * Private contructor.
     *
     * $options requires :
     *  'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     *  Optionally :
     *  'java_log_level' => null
     *  'java_send_size' => 8192,
     *  'java_recv_size' => 8192
     *
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ConnectionException
     *
     * @see PjbProxyClient::getInstance()
     *
     * @param array           $options
     * @param LoggerInterface $logger
     */
    protected function __construct(array $options, LoggerInterface $logger)
    {
        $this->options = new ArrayObject(array_merge($this->defaultOptions, $options));
        self::$instanceOptionsKey = serialize((array) $this->options);

        $this->logger = $logger;
        $this->loadClient();
    }

    /**
     * Return a unique instance of the phpjavabridge client
     * $options is an associative array and requires :.
     *
     *  'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     *  $options can be :
     *  "java_send_size" => 8192,
     *  "java_recv_size" => 8192,
     *  "use_persistent_connection" => false
     *  "java_log_level' => null,
     *  "java_prefer_values" => true (see note)
     *
     * <code>
     *    $options = [
     *      "servlet_address" => 'http://127.0.0.1:8080/javabridge-bundle/servlet.phpjavabridge'
     *      "java_send_size" => 8192,
     *      "java_recv_size" => 8192,
     *      "use_persistent_connection" => false,
     *      "internal_encoding" => 'UTF-8'
     *    ];
     *    $pjb = PjbProxyClient::getInstance($options, $logger);
     * </code>
     *
     * Note: java_prefer_values=true is the default working mode
     * of the soluble-japha client...
     *
     * Disadvantage: Not good for performance !!!
     *  > From mailinglist: Please note that the option JAVA_PREFER_VALUES kills performance as it
     *  > checks for an exception after each call (I.e. each java call generates a full network round-trip).
     *  Note that in simple_benchmarks.php no difference have been measured (localhost), need more
     *  taylor made tests to see.
     *
     * Advantage: More readable / writable
     *  > it casts java String, Boolean, Integer... objects
     *  > automatically into (string, bool, integer...), and thus
     *  > not require additional writing like ($ba->values($myInt)) in
     *  > order to get the value. (proxy)
     *
     * If you put at false you'll have to rework on the code.
     * Check what's best for yourself
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ConnectionException
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @param array|null           $options
     * @param LoggerInterface|null $logger  any psr3 logger
     *
     * @return PjbProxyClient
     */
    public static function getInstance(?array $options = null, ?LoggerInterface $logger = null): self
    {
        if (self::$instance === null) {
            if ($options === null) {
                throw new Exception\InvalidUsageException(
                    'Cannot instanciate PjbProxyClient without "$options" the first time, '.
                    'or the instance have been unregistered since'
                );
            }
            if ($logger === null) {
                $logger = new NullLogger();
            }
            self::$instance = new self($options, $logger);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return self::$instance !== null;
    }

    /**
     * Load pjb client with options.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ConnectionException
     */
    protected function loadClient(): void
    {
        if (self::$client === null) {
            $options = $this->options;

            if (!isset($options['servlet_address'])) {
                throw new Exception\InvalidArgumentException(__METHOD__.' Missing required parameter servlet_address');
            }

            $connection = static::parseServletUrl($options['servlet_address']);

            $params = new ArrayObject([
                Client::PARAM_JAVA_HOSTS => $connection['servlet_host'],
                Client::PARAM_JAVA_SERVLET => $connection['servlet_uri'],
                Client::PARAM_JAVA_AUTH_USER => $connection['auth_user'],
                Client::PARAM_JAVA_AUTH_PASSWORD => $connection['auth_password'],
                Client::PARAM_JAVA_DISABLE_AUTOLOAD => $options['java_disable_autoload'],
                Client::PARAM_JAVA_PREFER_VALUES => $options['java_prefer_values'],
                Client::PARAM_JAVA_SEND_SIZE => $options['java_send_size'],
                Client::PARAM_JAVA_RECV_SIZE => $options['java_recv_size'],
                Client::PARAM_JAVA_LOG_LEVEL => $options['java_log_level'],
                Client::PARAM_XML_PARSER_FORCE_SIMPLE_PARSER => $options['force_simple_xml_parser'],
                Client::PARAM_USE_PERSISTENT_CONNECTION => $options['use_persistent_connection']
            ]);

            self::$client = new Client($params, $this->logger);

            // Added in order to work with custom exceptions
            self::getClient()->throwExceptionProxyFactory = new Proxy\DefaultThrowExceptionProxyFactory(self::$client, $this->logger);

            $this->bootstrap();
        }
    }

    /**
     * Return Pjb62 internal client.
     *
     * @return Client
     *
     * @throws Exception\BrokenConnectionException
     */
    public static function getClient(): Client
    {
        if (self::$client === null) {
            throw new Exception\BrokenConnectionException('Client is not registered');
        }

        return self::$client;
    }

    /**
     * Return a Java class.
     *
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @param string $name Name of the java class
     *
     * @return JavaClass
     */
    public function getJavaClass($name): Interfaces\JavaClass
    {
        if (!array_key_exists($name, $this->classMapCache)) {
            $this->classMapCache[$name] = new JavaClass($name);
        }

        return $this->classMapCache[$name];
    }

    /**
     * Invoke a method dynamically.
     *
     * Example:
     * <code>
     * $bigint1 = new Java('java.math.BigInteger', 10);
     * $bigint2 = new Java('java.math.BigInteger', 20);
     * $bigint3 = PjbProxyClient::invokeMethod($bigint, "add", [$bigint2])
     * </code>
     *
     * <br> Any declared exception can be caught by PHP code. <br>
     * Exceptions derived from java.lang.RuntimeException or Error should
     * not be caught unless declared in the methods throws clause -- OutOfMemoryErrors cannot be caught at all,
     * even if declared.
     *
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaType|null $object a java object or type
     * @param string                   $method A method string
     * @param array                    $args   Arguments to send to method
     *
     * @return mixed
     */
    public function invokeMethod(?Interfaces\JavaType $object = null, string $method, array $args = [])
    {
        $id = ($object === null) ? 0 : $object->__getJavaInternalObjectId();

        return self::getClient()->invokeMethod($id, $method, $args);
    }

    /**
     * Inspect the java object | type.
     *
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaType $object
     *
     * @return string
     *
     * @throws IllegalArgumentException
     */
    public function inspect(Interfaces\JavaType $object): string
    {
        return self::getClient()->invokeMethod(0, 'inspect', [$object]);
    }

    /**
     * Test whether an object is an instance of java class or interface.
     *
     * @throws Exception\InvalidArgumentException
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject                                             $object
     * @param JavaType|string|Interfaces\JavaClass|Interfaces\JavaObject|string $class
     *
     * @return bool
     */
    public function isInstanceOf(Interfaces\JavaObject $object, $class): bool
    {
        if (is_string($class)) {
            // Attempt to initiate a class
            $name = $class;
            // Will eventually throws ClassNotFoundException
            $class = $this->getJavaClass($name);
        } elseif (!$class instanceof Interfaces\JavaObject) {
            throw new Exception\InvalidArgumentException(__METHOD__.'Class $class parameter must be of Interfaces\JavaClass, Interfaces\JavaObject or string');
        }

        return self::getClient()->invokeMethod(0, 'instanceOf', [$object, $class]);
    }

    /**
     * Evaluate a Java object.
     *
     * Evaluate a object and fetch its content, if possible. Use java_values() to convert a Java object into an equivalent PHP value.
     *
     * A java array, Map or Collection object is returned
     * as a php array.
     * An array, Map or Collection proxy is returned as a java array, Map or
     * Collection object, and a null proxy is returned as null.
     * All values of java types for which a primitive php type exists are
     * returned as php values.
     * Everything else is returned unevaluated.
     * Please make sure that the values do not not exceed
     * php's memory limit. Example:
     *
     *
     * <code>
     * $str = new java("java.lang.String", "hello");
     * echo java_values($str);
     * => hello
     * $chr = $str->toCharArray();
     * echo $chr;
     * => [o(array_of-C):"[C@1b10d42"]
     * $ar = java_values($chr);
     * print $ar;
     * => Array
     * print $ar[0];
     * => [o(Character):"h"]
     * print java_values($ar[0]);
     * => h
     * </code>
     *
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @param Interfaces\JavaObject $object
     *
     * @return mixed
     */
    public function getValues(Interfaces\JavaObject $object)
    {
        return self::getClient()->invokeMethod(0, 'getValues', [$object]);
    }

    /**
     * Return latest exception.
     *
     * @deprecated
     *
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     *
     * @return \Soluble\Japha\Bridge\Driver\Pjb62\Exception\JavaException
     */
    public function getLastException()
    {
        return self::getClient()->invokeMethod(0, 'getLastException', []);
    }

    /**
     * Clear last exception.
     *
     * @deprecated
     *
     * @throws \Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException
     */
    public function clearLastException(): void
    {
        self::getClient()->invokeMethod(0, 'clearLastException', []);
    }

    /**
     * @param Client $client
     *
     * @return string
     */
    public function getCompatibilityOption(Client $client = null): string
    {
        if ($this->compatibilityOption === null) {
            if ($client === null) {
                $client = $client = self::getClient();
            }

            $java_prefer_values = (int) $this->getOption('java_prefer_values');
            $java_log_level = $this->getOption('java_log_level');
            $compatibility = ($client->RUNTIME['PARSER'] === 'NATIVE') ? (0103 - $java_prefer_values) : (0100 + $java_prefer_values);
            if (is_int($java_log_level)) {
                $compatibility |= 128 | (7 & $java_log_level) << 2;
            }
            $this->compatibilityOption = \chr($compatibility);
        }

        return $this->compatibilityOption;
    }

    /**
     * Utility class to parse servlet_address,
     * i.e 'http://localhost:8080/javabridge-bundle/java/servlet.phpjavabridge'.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string $servlet_address
     *
     * @return array associative array with 'servlet_host' and 'servlet_uri'
     */
    public static function parseServletUrl(string $servlet_address): array
    {
        $url = parse_url($servlet_address);

        if ($url === false || !isset($url['host'])) {
            throw new Exception\InvalidArgumentException(__METHOD__." Cannot parse url '$servlet_address'");
        }

        $scheme = '';
        if (isset($url['scheme'])) {
            $scheme = $url['scheme'] === 'https' ? 'ssl://' : $scheme;
        }
        $host = $url['host'];
        $port = $url['port'];
        $path = $url['path'] ?? '';

        $infos = [
            'servlet_host' => "{$scheme}{$host}:{$port}",
            'servlet_uri' => $path,
            'auth_user' => $url['user'] ?? null,
            'auth_password' => $url['pass'] ?? null,
        ];

        return $infos;
    }

    /**
     * For compatibility usage all constants have been kept.
     */
    protected function bootstrap($options = []): void
    {
        register_shutdown_function(['Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', 'unregisterInstance']);
    }

    /**
     * Return options.
     *
     * @return ArrayObject
     */
    public function getOptions(): ArrayObject
    {
        return $this->options;
    }

    /**
     * Return specific option.
     *
     * @return mixed
     */
    public function getOption(string $name)
    {
        if (!$this->options->offsetExists($name)) {
            throw new Exception\InvalidArgumentException("Option '$name' does not exists'");
        }

        return $this->options[$name];
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @throws Exception\BrokenConnectionException|Exception\AuthenticationException
     *
     * @return never
     */
    public static function unregisterAndThrowBrokenConnectionException(string $message = null, int $code = null): void
    {
        if (self::$instance !== null) {
            $message = $message ?? 'undefined message';

            switch ($code) {
                case 401:
                    $exception = new Exception\AuthenticationException(sprintf(
                        'Java bridge authentication failure: code: %s',
                        $code
                    ));
                    break;
                default:
                    $exception = new Exception\BrokenConnectionException(sprintf(
                        'Java bridge broken connection: "%s" (code: %s)',
                        $message,
                        $code
                    ));
            }
            try {
                self::$instance->getLogger()->critical(sprintf(
                    '[soluble-japha] BrokenConnectionException to "%s": "%s" (code: "%s")',
                    self::$instance->getOption('servlet_address'),
                    $message,
                    $code ?? '?'
                ));
            } catch (\Throwable $e) {
                // discard logger errors
            }

            self::unregisterInstance();
            throw $exception;
        }
        throw new Exception\BrokenConnectionException('No instance to remove');
    }

    /**
     * Clean up PjbProxyClient instance.
     */
    public static function unregisterInstance(): void
    {
        if (!self::$unregistering && self::$client !== null) {
            self::$unregistering = true;

            if ((self::$client->preparedToSendBuffer ?: '') !== '') {
                self::$client->sendBuffer .= self::$client->preparedToSendBuffer;
            }

            try {
                self::$client->sendBuffer .= self::$client->protocol->getKeepAlive();
                self::$client->protocol->flush();
            } catch (\Throwable $e) {
            }

            // TODO MUST TEST, IT WAS REMOVED FROM FUNCTION
            // BECAUSE IT SIMPLY LOOKS LIKE THE LINES BEFORE
            // ADDED AN IF TO CHECK THE CHANNEL In CASE OF
            //
            if (isset(self::$client->protocol->handler->channel) &&
                false === strpos(get_class(self::getClient()->protocol->handler->channel), '/EmptyChannel/')) {
                try {
                    self::$client->protocol->keepAlive();
                } catch (\Throwable $e) {
                    // silently discard exceptions when unregistering
                }
            }

            self::$client = null;
            self::$instance = null;
            self::$instanceOptionsKey = null;
            self::$unregistering = false;
        }
    }
}

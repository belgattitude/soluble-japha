<?php

/**
 * Soluble Japha / PhpJavaBridge
 *
 * @author Vanvelthem Sébastien
 * @license MIT
 *
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Exception;
use Soluble\Japha\Interfaces;
use ArrayObject;

class PjbProxyClient
{
    /**
     *
     * @var PjbProxyClient
     */
    protected static $instance;

    /**
     *
     * @var array
     */
    protected $defaultOptions = [
        'java_disable_autoload' => false,
        'java_prefer_values' => true,
        'java_log_level' => null,
        'java_send_size' => 8192,
        'java_recv_size' => 8192
    ];

    /**
     *
     * @var Client|null
     */
    protected static $client;

    /**
     * Internal cache for already loaded Java classes
     * @var array
     */
    protected $classMapCache = [];

    /**
     *
     * @var string
     */
    protected $compatibilityOption;

    /**
     *
     * @var array|null
     */
    public $options;

    /**
     *
     * @var string|null
     */
    protected static $instanceOptionsKey;

    /**
     * Private contructor
     *
     * $options requires :
     *  'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     *  Optionnaly :
     *  'java_disable_autoload' => false,
     *  'java_prefer_values' => true,
     *  'java_log_level' => null
     *  'java_send_size' => 8192,
     *  'java_recv_size' => 8192
     *
     *
     * @throws Exception\InvalidArgumentException
     * @see PjbProxyClient::getInstance()
     * @param array $options
     */
    private function __construct(array $options)
    {
        self::$instanceOptionsKey = serialize($options);
        $this->options = array_merge($options, $this->defaultOptions);
        $this->loadClient();
    }


    /**
     * Return a unique instance of the phpjavabridge client
     * $options is an associative array and requires :
     *
     *  'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     *  $options can be :
     *  "java_log_level' => null
     *  "java_send_size" => 8192,
     *  "java_recv_size" => 8192
     *
     * <code>
     *    $options = [
     *      'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/servlet.phpjavabridge'
     *      "java_send_size" => 8192,
     *      "java_recv_size" => 8192
     *
     *    ];
     *    $pjb = PjbProxyClient::getInstance($options);
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidUsageException
     * @param array|null $options
     * @return PjbProxyClient
     */
    public static function getInstance(array $options = null)
    {
        if (self::$instance === null) {
            self::$instance = new PjbProxyClient($options);
        }
        /* todo order array
        elseif (is_array($options) && self::$instanceOptionsKey != serialize($options)) {
            $message  = 'PjbProxyClient::getInstance should only be configured once (bootstrap) with $option parameter';
            throw new Exception\InvalidUsageException(__METHOD__ . $message);
        } */
        return self::$instance;
    }

    /**
     *
     * @return bool
     */
    public static function isInitialized()
    {
        return (self::$instance !== null);
    }


    /**
     * Load pjb client with options
     *

     * @throws Exception\InvalidArgumentException
     */
    protected function loadClient()
    {
        if (self::$client === null) {
            $options = $this->options;

            if (!isset($options['servlet_address'])) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Missing required parameter servlet_address");
            }

            $connection = $this->parseServletUrl($options['servlet_address']);


            /*
            define("JAVA_HOSTS", $connection["servlet_host"]);
            define("JAVA_SERVLET", $connection["servlet_uri"]);
            define("JAVA_DISABLE_AUTOLOAD", $options['java_disable_autoload']);
            define('JAVA_PREFER_VALUES', $options['java_prefer_values']);
            define('JAVA_SEND_SIZE', $options['java_send_size']);
            define('JAVA_RECV_SIZE', $options['java_recv_size']);
            */
            /*
            if (!defined('JAVA_PREFER_VALUES')) {
                define('JAVA_PREFER_VALUES', $options['java_prefer_values']);
            }*/



            $params = new ArrayObject();
            $params['JAVA_HOSTS'] = $connection["servlet_host"];
            $params['JAVA_SERVLET'] = $connection["servlet_uri"];
            $params['JAVA_DISABLE_AUTOLOAD'] = $options['java_disable_autoload'];
            $params['JAVA_PREFER_VALUES'] =  $options['java_prefer_values'];
            $params['JAVA_SEND_SIZE'] = $options['java_send_size'];
            $params['JAVA_RECV_SIZE'] = $options['java_recv_size'];
            $params['JAVA_LOG_LEVEL'] = $options['java_log_level'];

            self::$client = new Client($params);

            // Added in order to work with custom exceptions
            self::$client->throwExceptionProxyFactory = new Proxy\DefaultThrowExceptionProxyFactory(self::$client);

            $this->bootstrap();

        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return self::$client;
    }

    /**
     * Return a Java class
     *
     * @param string $name Name of the java class
     * @return JavaClass
     */
    public function getJavaClass($name)
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
     * @param JavaType $object A java object or type
     * @param string $method A method string
     * @param mixed $args Arguments to send to method
     * @return mixed
     */
    public function invokeMethod($object, $method, array $args=[])
    {
        $id = ($object == null) ? 0 : $object->__java;
        return self::$client->invokeMethod($id, $method, $args);
    }


    /**
     *
     * @param JavaType $object
     * @return string
     * @throws Exception\IllegalArgumentException
     */
    public function inspect(JavaType $object)
    {
        //$client = self::getClient();
        //return $client->invokeMethod(0, "inspect", array($object));
        return self::$client->invokeMethod(0, "inspect", [$object]);
    }

    /**
     * Test whether an object is an instance of java class or interface
     *
     * @throws Exception\InvalidArgumentException
     * @param JavaType|Interfaces\JavaObject $object
     * @param JavaType|string|Interfaces\JavaClass|Interfaces\JavaObject|string $class
     * @return boolean
     */
    public function isInstanceOf(JavaType $object, $class)
    {
        if (is_string($class)) {
            // Attempt to autoload classname
            $name = $class;
            try {
                $class = $this->getJavaClass($name);
            } catch (\Exception $e) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Class '$name' not found and cannot be resolved for comparison.");
            }
        }

        if (!$class instanceof JavaType) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Invalid argument, class parameter must be a valid JavaType or class name as string");
        }
        return self::$client->invokeMethod(0, "instanceOf", [$object, $class]);
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
     * @param JavaType $object
     * @return mixed
     */
    public function getValues(JavaType $object)
    {
        return self::$client->invokeMethod(0, "getValues", [$object]);
    }

    /**
     * @return JavaException
     */
    public function getLastException()
    {
        return self::$client->invokeMethod(0, "getLastException", []);
    }

    /**
     */
    public function clearLastException()
    {
        self::$client->invokeMethod(0, "clearLastException", []);
    }

    /**
     *
     * @param Client $client
     * @return string
     */
    public function getCompatibilityOption($client)
    {
        if ($this->compatibilityOption === null) {
            $client = self::getClient();
            $java_prefer_values = $this->getOption('java_prefer_values');
            $java_log_level = $this->getOption('java_log_level');
            @$compatibility = $client->RUNTIME["PARSER"] == "NATIVE" ? (0103 - $java_prefer_values) : (0100 + $java_prefer_values);
            if (@is_int($java_log_level)) {
                $compatibility |=128 | (7 & $java_log_level) << 2;
            }
            $this->compatibilityOption = chr($compatibility);
        }

        return $this->compatibilityOption;
    }





    /**
     * Utility class to parse servlet_address,
     * i.e 'http://localhost:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     * @throws Exception\InvalidArgumentException
     * @param string $servlet_address
     * @return array associative array with 'servlet_host' and 'servlet_uri'
     */
    protected function parseServletUrl($servlet_address)
    {
        $url = parse_url($servlet_address);
        if ($url === false || !isset($url['host'])) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Cannot parse url '$servlet_address'");
        }

        $scheme = '';
        if (isset($url['scheme'])) {
            $scheme = $url['scheme'] == 'https' ? 'ssl://' : $scheme;
        }
        $host = $url['host'];
        $port = $url["port"];
        $path = isset($url["path"]) ? $url['path'] : '';

        $infos = [
            'servlet_host' => "${scheme}${host}:${port}",
            'servlet_uri' => "$path",
        ];
        return $infos;
    }




    /**
     * For compatibility usage all constants have been kept
     */
    protected function bootstrap($options = [])
    {

        /// BOOTSTRAP
        /// A lot to rework, remove constants
        //define("JAVA_PEAR_VERSION", "6.2.1");

        /*
        if (!defined("JAVA_DISABLE_AUTOLOAD") || !JAVA_DISABLE_AUTOLOAD) {
            //spl_autoload_register(array(__CLASS__, "autoload"));
            spl_autoload_register(array('Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', "autoload"));
        }

        */

        //register_shutdown_function(array(__CLASS__, 'shutdown'));
        register_shutdown_function(['Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', 'unregisterInstance']);

        /*
        if (!defined("JAVA_SEND_SIZE")) {
            define("JAVA_SEND_SIZE", 8192);
        }
        if (!defined("JAVA_RECV_SIZE")) {
            define("JAVA_RECV_SIZE", 8192);
        }

        if (!defined("JAVA_HOSTS")) {
            if (!java_defineHostFromInitialQuery(java_get_base())) {
                if ($java_ini = get_cfg_var("java.hosts")) {
                    define("JAVA_HOSTS", $java_ini);
                } else {
                    define("JAVA_HOSTS", "127.0.0.1:8080");
                }
            }
        }

        if (!defined("JAVA_SERVLET")) {
            if (!(($java_ini = get_cfg_var("java.servlet")) === false)) {
                define("JAVA_SERVLET", $java_ini);
            } else {
                define("JAVA_SERVLET", 1);
            }
        }*/

/*
        if (!defined("JAVA_LOG_LEVEL")) {
            if (!(($java_ini = get_cfg_var("java.log_level")) === false)) {
                define("JAVA_LOG_LEVEL", (int) $java_ini);
            }
        } else {
            define("JAVA_LOG_LEVEL", null);
        }
        if (!defined("JAVA_PREFER_VALUES")) {
            if ($java_ini = get_cfg_var("java.prefer_values")) {
                define("JAVA_PREFER_VALUES", $java_ini);
            }
        }
*/
    }

    /**
     * Return options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return specific option
     * @param $name
     * @return mixed
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new Exception\InvalidArgumentException("Option '$name' does not exists'");
        }
        return $this->options[$name];
    }

    /**
     * Clean up PjbProxyClient instance
     * @return void
     */
    public static function unregisterInstance()
    {
        if (self::isInitialized()) {
            // TODO CHECK WITH SESSIONS
            if (session_id()) {
                session_write_close();
            }
            if (!isset(self::$client->protocol) || self::$client->inArgs) {
                return;
            }
            if (self::$client->preparedToSendBuffer) {
                self::$client->sendBuffer .= self::$client->preparedToSendBuffer;
            }

            self::$client->sendBuffer.= self::$client->protocol->getKeepAlive();


            self::$client->protocol->flush();

            // TODO MUST TEST, IT WAS REMOVED FROM FUNCTION
            // BECAUSE IT SIMPLY LOOKS LIKE THE LINES BEFORE
            // ADDED AN IF TO CHECK THE CHANNEL In CASE OF
            //
            if (isset(self::$client->protocol->handler->channel) &&
                    !preg_match('/EmptyChannel/', get_class(self::$client->protocol->handler->channel))) {
                try {
                    self::$client->protocol->keepAlive();
                } catch (\Exception $e) {
                    // silently discard exceptions when unregistering
                }
            }

            // Added but needs more tests
            //unset($client);// = null;

            self::$client = null;
            self::$instance = null;
            self::$instanceOptionsKey = null;
        }
    }


    /**
     * Before removing instance
     * @return void
     */
    public function __destroy()
    {
        $this->unregisterInstance();
    }
}

<?php

/**
 * Soluble Japha / PhpJavaBridge
 *
 * @author Vanvelthem Sébastien
 * @license   MIT
 *
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Exception;
use Soluble\Japha\Interfaces;

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
    protected $defaultOptions = array(
        'java_disable_autoload' => false,
        'java_prefer_values' => true,
        'load_pjb_compatibility' => true
    );


    /**
     *
     * @var Client
     */
    protected $client;

    /**
     *
     * @var array
     */
    protected $classMapCache = array();

    /**
     *
     * @var string
     */
    protected $compatibilityOption;

    /**
     *
     * @param array $options
     */
    private function __construct($options)
    {
        $this->loadClient($options);
    }


    /**
     * Return a unique instance of the phpjavabridge client
     * $options is an associative array and requires :
     *
     *  'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     *  Optionnaly :
     *  "java_disable_autoload' => false,
     *  "java_prefer_values' => true,
     *  "load_pjb_compatibility' => false
     *
     * <code>
     *    $options = [
     *      "servlet_host" => "http://127.0.0.1:8080"
     *      "servlet_uri"  => "/javabridge-bundle/java/servlet.phpjavabridge"
     *      //"java_disable_autoload' => false,
     *      //"java_prefer_values' => true,
     *      //"load_pjb_compatibility' => false
     *    ];
     *    $pjb = PjbProxyClient::getInstance($options);
     * </code>
     *
     * @param array $options
     * @return PjbProxyClient
     */
    public static function getInstance($options = array())
    {
        if (self::$instance === null) {
            self::$instance = new PjbProxyClient($options);
        }
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
     * $options is an associative array and requires :
     *
     *  'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/java/servlet.phpjavabridge'
     *
     *  Optionnaly :
     *  "java_disable_autoload' => false,
     *  "java_prefer_values' => true,
     *  "load_pjb_compatibility' => false
     *
     * @throws Exception\InvalidArgumentException
     * @param array $options
     */
    protected function loadClient(array $options)
    {
        if ($this->client === null) {
            if (!isset($options['servlet_address'])) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Missing required parameter servlet_address");
            }

            $connection = $this->parseServletUrl($options['servlet_address']);

            $opts = array_merge($options, $this->defaultOptions);

            define("JAVA_HOSTS", $connection["servlet_host"]);
            define("JAVA_SERVLET", $connection["servlet_uri"]);
            define("JAVA_DISABLE_AUTOLOAD", $opts['java_disable_autoload']);
            define('JAVA_PREFER_VALUES', $opts['java_prefer_values']);


            if ($opts['load_pjb_compatibility']) {
                $ds = DIRECTORY_SEPARATOR;
                require_once dirname(__FILE__) . $ds . "Compat" . $ds . "pjb_functions.php";
            }

            $this->client = new Client();

            // Added in order to work with custom exceptions
            $this->client->throwExceptionProxyFactory = new Proxy\DefaultThrowExceptionProxyFactory($this->client);

            $this->boostrap();
        }
    }

    /**
     *
     */
    public function getClient()
    {
        return $this->client;
    }

    public static function shutdown()
    {
        if (self::isInitialized()) {
            $client = self::getInstance()->getClient();

            // TODO CHECK WITH SESSIONS
            if (session_id()) {
                session_write_close();
            }
            if (!isset($client->protocol) || $client->inArgs) {
                return;
            }
            if ($client->preparedToSendBuffer) {
                $client->sendBuffer .= $client->preparedToSendBuffer;
            }

            $client->sendBuffer.= $client->protocol->getKeepAlive();


            $client->protocol->flush();

            // TODO MUST TEST, IT WAS REMOVED FROM FUNCTION
            // BECAUSE IT SIMPLY LOOKS LIKE THE LINES BEFORE
            // ADDED AN IF TO CHECK THE CHANNEL In CASE OF
            //

            if (isset($client->protocol->handler->channel) &&
                    !preg_match('/EmptyChannel/', get_class($client->protocol->handler->channel))) {
                $client->protocol->keepAlive();
            }


            // Added but needs more tests
            //$client = null;

            self::$instance = null;
        }
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

     * Example:
     * <code>
     * PjbProxyClient::invokeJavaMethod(new java("java.lang.String","hello"), "toString", array())
     * </code>
     *
     * <br> Any declared exception can be caught by PHP code. <br>
     * Exceptions derived from java.lang.RuntimeException or Error should
     * not be caught unless declared in the methods throws clause -- OutOfMemoryErrors cannot be caught at all,
     * even if declared.
     *
     * @param JavaType $object A java object or type
     * @param string $method A method string
     * @param array $args An argument array
     */
    public function invokeMethod($object, $method, $args)
    {
        $id = ($object == null) ? 0 : $object->__java;
        return $this->client->invokeMethod($id, $method, $args);
    }

    /**
     *
     * @param mixed $x
     * @return boolean
     */
    public static function autoload5($x)
    {
        $c = self::$instance->getClient();
        if ($c) {
            $s = str_replace("_", ".", $x);
            if (!($c->invokeMethod(0, "typeExists", array($s)))) {
                return false;
            }
            $i = "class ${x} extends Java {" .
                    "static function type(\$sub=null) {if(\$sub) \$sub='\$'.\$sub; return java('${s}'.\"\$sub\");}" .
                    'function __construct() {$args=func_get_args();' .
                    'array_unshift($args,' . "'$s'" . '); parent::__construct($args);}}';
            eval("$i");
            return true;
        }
        return false;
    }

    /**
     *
     * @param mixed $x
     * @return boolean
     */
    public static function autoload($x)
    {
        $client = self::$instance->getClient();

        if ($client !== null) {
            $idx = strrpos($x, "\\");
            if (!$idx) {
                return java_autoload_function5($x);
            }
            $str = str_replace("\\", ".", $x);


            if (!($client->invokeMethod(0, "typeExists", array($str)))) {
                return false;
            }
            $package = substr($x, 0, $idx);
            $name = substr($x, 1 + $idx);
            $instance = "namespace $package; class ${name} extends \\Java {" .
                    "static function type(\$sub=null) {if(\$sub) \$sub='\$'.\$sub;return \\java('${str}'.\"\$sub\");}" .
                    "static function __callStatic(\$procedure,\$args) {return \\java_invoke(\\java('${str}'),\$procedure,\$args);}" .
                    'function __construct() {$args=func_get_args();' .
                    'array_unshift($args,' . "'$str'" . '); parent::__construct($args);}}';
            eval("$instance");
            return true;
        }
        return false;
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
        return $this->client->invokeMethod(0, "inspect", array($object));
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
        /*
        if (!$object instanceof JavaType) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Invalid argument, object parameter must be a valid JavaType");
        }
        */

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
        return $this->client->invokeMethod(0, "instanceOf", array($object, $class));
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
    public function getValues($object)
    {
        if (!$object instanceof JavaType) {
            return $object;
        }

        return $this->client->invokeMethod(0, "getValues", array($object));
    }

    /**
     * @return JavaException
     */
    public function getLastException()
    {
        return $this->client->invokeMethod(0, "getLastException", array());
    }

    /**
     */
    public function clearLastException()
    {
        $this->client->invokeMethod(0, "clearLastException", array());
    }

    /**
     *
     * @param Client $client
     * @return type
     */
    public function getCompatibilityOption($client)
    {
        if ($this->compatibilityOption === null) {
            $client = self::getClient();
            @$compatibility = $client->RUNTIME["PARSER"] == "NATIVE" ? (0103 - JAVA_PREFER_VALUES) : (0100 + JAVA_PREFER_VALUES);
            if (@is_int(JAVA_LOG_LEVEL)) {
                $compatibility |=128 | (7 & JAVA_LOG_LEVEL) << 2;
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

        $infos = array(
            'servlet_host' => "${scheme}${host}:${port}",
            'servlet_uri' => "$path",
        );
        return $infos;
    }


    /**
     * For compatibility usage all constants have been kept
     */
    protected function boostrap($options = array())
    {

        /// BOOTSTRAP
        /// A lot to rework, remove constants
        //define("JAVA_PEAR_VERSION", "6.2.1");

        if (!defined("JAVA_DISABLE_AUTOLOAD") || !JAVA_DISABLE_AUTOLOAD) {
            spl_autoload_register(array(__CLASS__, "autoload"));
        }
        register_shutdown_function(array(__CLASS__, 'shutdown'));


        if (!defined("JAVA_SEND_SIZE")) {
            define("JAVA_SEND_SIZE", 8192);
        }
        if (!defined("JAVA_RECV_SIZE")) {
            define("JAVA_RECV_SIZE", 8192);
        }

        /*
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
    }
}

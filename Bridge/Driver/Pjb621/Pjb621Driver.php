<?php

namespace Soluble\Japha\Bridge\Driver\Pjb621;

use Soluble\Japha\Bridge\Driver\AbstractDriver;
use Soluble\Japha\Bridge\JavaObjectInterface;

class Pjb621Driver extends AbstractDriver
{
    
    protected $connected = false;

    /**
     *
     * @var Client
     */
    protected $client;
    
    /**
     *
     * @var string $java_server_url i.e. 127.0.0.1
     */
    function __construct($java_server_url)
    {
        define("JAVA_HOSTS", "$java_server_url");
        define("JAVA_DISABLE_AUTOLOAD", true);
        define('JAVA_PREFER_VALUES', false);
        require_once dirname(__FILE__) . "/functions.php";
    }

    function getClient()
    {
        if (!$this->connected) {
            $this->connect();
        }
        if ($this->client === null) {
            $this->client = new Client();
        }
        return $this->client;
    }

    function connect()
    {
        if (!$this->connected) {
            $this->connected = true;
        }
    }
    
    
    /**
     * Return a new java class
     *
     * @param string $class_name
     * @return JavaClass
     */
    function getJavaClass($class_name)
    {
        /*
        if (!array_key_exists($java_class_name, self::$classMap)) {
            self::$classMap[$java_class_name] = new Pjb\JavaClass($java_class_name);
        }
        return self::$classMap[$java_class_name];         

         */

        return java($class_name);
    }
    
    
    /**
     * Instanciate a java object
     *
     * @param string $class_name
     * @param array $args
     * @return Java
     */
    function instanciate($class_name, $args = array())
    {
        return new Java($class_name, $args);
    }
    
    

    /**
     *
     *
     * @param JavaObjectInterface $javaObject
     * @return string
     */
    function inspect(JavaObjectInterface $javaObject)
    {
        return java_inspect($javaObject);
    }
    
    
    /**
     * Checks
     *
     * @param JavaObjectInterface $javaObject
     * @param string $className
     * @return boolean
     */
    function isInstanceOf(JavaObjectInterface $javaObject, $className)
    {
        return java_instanceof($javaObject, $className);
    }
    
    /**
     *
     *
     * @param JavaObjectInterface $javaObject
     * @return mixed
     */
    function values(JavaObjectInterface $javaObject)
    {
        return java_values($javaObject);
    }
    
    
    
    /**
     * Return object java class name
     *
     * @param JavaObjectInterface $javaObject
     * @return string
     */
    function getClassName(JavaObjectInterface $javaObject)
    {
        $inspect = $this->inspect($javaObject);
        // [class java.sql.DriverManager:
        $matches = array();
        preg_match('/^\[class (.+)\:/', $inspect, $matches);
        if (!isset($matches[1]) || $matches[1] == '') {
            throw new \Exception(__METHOD__ . " Cannot determine class name");
        }
        return $matches[1];
        
        
    }
}

<?php

namespace Soluble\Japha\Bridge\Driver\Pjb621;

use Soluble\Japha\Bridge\Driver\AbstractDriver;
use Soluble\Japha\Interfaces;
use Soluble\Japha\Bridge\Exception;

class Pjb621Driver extends AbstractDriver
{
    protected $connected = false;

    /**
     *
     * @var PjbProxyClient
     */
    protected $pjbProxyClient;
    
    /**
     *
     * @var string $java_server_url i.e. 127.0.0.1
     */
    public function __construct($java_server_url)
    {
        define("JAVA_HOSTS", "$java_server_url");
        define("JAVA_DISABLE_AUTOLOAD", false);
        define('JAVA_PREFER_VALUES', false);
        require_once dirname(__FILE__) . "/compatibility.php";
        
        $this->pjbProxyClient = PjbProxyClient::getInstance();
    }

    /**
     * Return underlying bridge client
     * @return PjbProxyClient
     */
    public function getClient()
    {
        return $this->pjbProxyClient;
    }

    public function connect()
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
    public function getJavaClass($class_name)
    {
        /*
        if (!array_key_exists($java_class_name, self::$classMap)) {
            self::$classMap[$java_class_name] = new Pjb\JavaClass($java_class_name);
        }
        return self::$classMap[$java_class_name];

         */

        return $this->pjbProxyClient->getJavaClass($class_name);
    }
    
    
    /**
     * Instanciate a java object
     *
     * @param string $class_name
     * @param mixed|null $args
     * @return Java
     */
    public function instanciate($class_name, $args = null)
    {
        //return $this->pjbProxyClient->getJavaClass($class_name, $args);

        return new Java($class_name, $args);
    }
    
    

    /**
     *
     *
     * @param Interfaces\JavaObject $javaObject
     * @return string
     */
    public function inspect(Interfaces\JavaObject $javaObject)
    {
        return $this->pjbProxyClient->inspect($javaObject);
    }
    
    
    /**
     * Checks whether object is an instance of a class or interface
     *
     * @param Interfaces\JavaObject $javaObject
     * @param string $className java class name
     * @return boolean
     */
    public function isInstanceOf(Interfaces\JavaObject $javaObject, $className)
    {
        return $this->pjbProxyClient->isInstanceOf($javaObject, $className);
    }
    
    /**
     *
     *
     * @param Interfaces\JavaObject $javaObject
     * @return mixed
     */
    public function values(Interfaces\JavaObject $javaObject)
    {
        return $this->pjbProxyClient->getValues($javaObject);
    }
    
    
    
    /**
     * Return object java class name
     *
     * @throw Exception\UnexpectedException
     * @param Interfaces\JavaObject $javaObject
     * @return string
     */
    public function getClassName(Interfaces\JavaObject $javaObject)
    {
        $inspect = $this->inspect($javaObject);
        // [class java.sql.DriverManager:
        $matches = array();
        preg_match('/^\[class (.+)\:/', $inspect, $matches);
        if (!isset($matches[1]) || $matches[1] == '') {
            throw new Exception\UnexpectedException(__METHOD__ . " Cannot determine class name");
        }
        return $matches[1];
    }
}

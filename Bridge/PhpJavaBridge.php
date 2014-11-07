<?php

namespace Soluble\Japha\Bridge;

use Soluble\Japha\Bridge\Pjb621 as Pjb;

class PhpJavaBridge
{

    /**
     *
     * @var Pjb\Client 
     */
    static protected $client;
    
    /**
     *
     * @var array
     */
    static protected $classMap = array();
    
    /**
     * @return Pjb\Client
     */
    static function getClient()
    {
        if (!self::$client) {
            self::$client = new Pjb\Client();
        }
        return self::$client;
    }
    
    /**
     * Return a new java class object
     * @param string Java class full qualified name 
     * @return Pjb\JavaClass
     */
    static function java($java_class_name)
    {
        if (!array_key_exists($java_class_name, self::$classMap)) {
            self::$classMap[$java_class_name] = new Pjb\JavaClass($java_class_name);
        }
        return self::$classMap[$java_class_name];     
    }
    
    
    
    /**
	 * @var boolean 
	 */
	protected static $bridge_loaded;


	/**
	 * Include remote javabridge and check if it's available
	 *
	 * @throws Exception\NotAvailableException
	 * @param string $bridge_address "ip:port"
	 * @return void
	 */
	static function includeBridge($bridge_address)
	{
		if (!self::$bridge_loaded) {
			//self::checkBridge($bridge_address);
			//require_once($bridge_address);
            
           // define ("JAVA_PREFER_VALUES", false); <- a bug see, functions.php
            define ("JAVA_HOSTS", "192.125.12.1:8083");
            define ("JAVA_DISABLE_AUTOLOAD", true);
            define('JAVA_PREFER_VALUES', false);
            require dirname(__FILE__) . '/Pjb621/functions.php';
            
			self::$bridge_loaded = true;
		}
	}
    
    

}

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
            define ("JAVA_HOSTS", "127.0.0.1:8083");
            define ("JAVA_DISABLE_AUTOLOAD", true);
            define('JAVA_PREFER_VALUES', true);
            require dirname(__FILE__) . '/Pjb621/functions.php';
            
			self::$bridge_loaded = true;
		}
	}

	/**
	 * Check if bridge is available
	 *
	 * @throws Exception\NotAvailableException
	 * @param string $bridge_address "ip:port"
	 * @return void
	 */
	static protected function checkBridge($bridge_address) 
	{
        /*
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $bridge_address);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // no output
		curl_setopt($ch, CURLOPT_FAILONERROR, true); // fail if a http error is received
		// grab URL and pass it to the browser
		curl_exec($ch);
		$errno = curl_errno($ch);
		if ($errno > 0) {
			throw new Exception\NotAvailableException(__METHOD__ . " [JavaBridgeError] Java bridge server cannot be reached on '$bridge_address' [Curl error code: $errno], contact our webmaster.");
		}
		curl_close($ch);
		return true;
         * 
         */
	}
}

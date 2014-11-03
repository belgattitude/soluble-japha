<?php

namespace Soluble\Japha\Bridge;

class PhpJavaBridge
{
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
			self::checkBridge($bridge_address);
			require_once($bridge_address);
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
	}
}

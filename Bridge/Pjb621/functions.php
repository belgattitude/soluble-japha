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



# Java.inc -- The PHP/Java Bridge PHP library. Compiled from JavaBridge.inc.
# Copyright (C) 2003-2009 Jost Boekemeier.
# Distributed under the MIT license, see Options.inc for details.
# Customization examples:
# define ("JAVA_HOSTS", 9267); define ("JAVA_SERVLET", false);
# define ("JAVA_HOSTS", "127.0.0.1:8787");
# define ("JAVA_HOSTS", "ssl://my-secure-host.com:8443");
# define ("JAVA_SERVLET", "/MyWebApp/servlet.phpjavabridge");
# define ("JAVA_PREFER_VALUES", 1);

namespace Soluble\Japha\Bridge\Pjb621 {

    function java_get_base()
    {
        $ar = get_required_files();
        $arLen = sizeof($ar);
        if ($arLen > 0) {
            $thiz = $ar[$arLen - 1];
            return dirname($thiz);
        } else {
            return "java";
        }
    }

    function java_truncate($str)
    {
        if (strlen($str) > 955) {
            return substr($str, 0, 475) . '[...]' . substr($str, -475);
        }
        return $str;
    }

    function java_autoload_function5($x)
    {
        $s = str_replace("_", ".", $x);
        $c = __javaproxy_Client_getClient();
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

    function java_autoload_function($x)
    {
        $idx = strrpos($x, "\\");
        if (!$idx) {
            return java_autoload_function5($x);
        }
        $str = str_replace("\\", ".", $x);
        $client = __javaproxy_Client_getClient();
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

    if (!defined("JAVA_DISABLE_AUTOLOAD") && function_exists("spl_autoload_register")) {
        spl_autoload_register(__NAMESPACE__ . "\java_autoload_function");
    }

    function java_autoload($libs = null)
    {
        trigger_error('Please use <a href="http://php-java-bridge.sourceforge.net/pjb/webapp.php>tomcat or jee hot deployment</a> instead', E_USER_WARNING);
    }

    function java_virtual($path, $return = false)
    {
        $req = java_context()->getHttpServletRequest();
        $req = new java("php.java.servlet.VoidInputHttpServletRequest", $req);
        $res = java_context()->getHttpServletResponse();
        $res = new java("php.java.servlet.RemoteHttpServletResponse", $res);
        $req->getRequestDispatcher($path)->include($req, $res);
        if ($return) {
            return $res->getBufferContents();
        }
        echo $res->getBufferContents();
        return true;
    }

    function java($name)
    {
        static $classMap = array();
        if (array_key_exists($name, $classMap)) {
            return $classMap[$name];
        }
        return $classMap[$name] = new JavaClass($name);
    }

    function java_get_closure()
    {
        return java_closure_array(func_get_args());
    }

    function java_wrap()
    {
        return java_closure_array(func_get_args());
    }

    function java_get_values($arg)
    {
        return java_values($arg);
    }

    function java_get_session()
    {
        return java_session_array(func_get_args());
    }

    function java_get_context()
    {
        return java_context();
    }

    function java_get_server_name()
    {
        return java_server_name();
    }

    function java_isnull($value)
    {
        return is_null(java_values($value));
    }

    function java_is_null($value)
    {
        return is_null(java_values($value));
    }

    function java_istrue($value)
    {
        return (boolean) (java_values($value));
    }

    function java_is_true($value)
    {
        return (boolean) (java_values($value));
    }

    function java_isfalse($value)
    {
        return !(java_values($value));
    }

    function java_is_false($value)
    {
        return !(java_values($value));
    }

    function java_set_encoding($enc)
    {
        return java_set_file_encoding($enc);
    }
    function java_call_with_continuation($kontinuation = null)
    {
        if (java_getHeader("X_JAVABRIDGE_INCLUDE", $_SERVER) && !java_getHeader("X_JAVABRIDGE_INCLUDE_ONLY", $_SERVER)) {
            if (is_null($kontinuation)) {
                java_context()->call(java_closure());
            } elseif (is_string($kontinuation))
            java_context()->call(call_user_func($kontinuation));
            elseif ($kontinuation instanceof JavaType)
            java_context()->call($kontinuation);
            else {
                java_context()->call(java_closure($kontinuation));
            }
        }
    }

    function java_defineHostFromInitialQuery($java_base)
    {
        if ($java_base != "java") {
            $url = parse_url($java_base);
            if (isset($url["scheme"]) && ($url["scheme"] == "http" || $url["scheme"] == "https")) {
                $scheme = $url["scheme"] == "https" ? "ssl://" : "";
                $host = $url["host"];
                $port = $url["port"];
                $path = $url["path"];
                define("JAVA_HOSTS", "${scheme}${host}:${port}");
                $dir = dirname($path);
                define("JAVA_SERVLET", "$dir/servlet.phpjavabridge");
                return true;
            }
        }
        return false;
    }
    define("JAVA_PEAR_VERSION", "6.2.1");
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
    }     if (!defined("JAVA_SERVLET")) {
        if (!(($java_ini = get_cfg_var("java.servlet")) === false)) {
            define("JAVA_SERVLET", $java_ini);
        } else {
            define("JAVA_SERVLET", 1);
        }
    }     if (!defined("JAVA_LOG_LEVEL")) {
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

    function java_shutdown()
    {
        global $java_initialized;
        if (!$java_initialized) {
            return;
        }
        if (session_id()) {
            session_write_close();
        }
        $client = __javaproxy_Client_getClient();
        if (!isset($client->protocol) || $client->inArgs) {
            return;
            }
        if ($client->preparedToSendBuffer) {
            $client->sendBuffer.=$client->preparedToSendBuffer;
            }
        $client->sendBuffer.=$client->protocol->getKeepAlive();
        $client->protocol->flush();
        $client->protocol->keepAlive();
    }

    register_shutdown_function(__NAMESPACE__ . "\java_shutdown");

    function java_getHeader($name, $array)
    {
        if (array_key_exists($name, $array)) {
            return $array[$name];
        }
        $name = "HTTP_$name";
        if (array_key_exists($name, $array)) {
            return $array[$name];
        }
        return null;
    }

    function java_checkCliSapi()
    {
        $sapi = substr(php_sapi_name(), 0, 3);
        return ((($sapi == 'cgi') && !get_cfg_var("java.session")) || ($sapi == 'cli'));
    }

    function java_getCompatibilityOption($client)
    {
        static $compatibility = null;
        if ($compatibility) {
            return $compatibility;
        }
        @$compatibility = $client->RUNTIME["PARSER"] == "NATIVE" ? (0103 - JAVA_PREFER_VALUES) : (0100 + JAVA_PREFER_VALUES);
        if (@is_int(JAVA_LOG_LEVEL)) {
            $compatibility |=128 | (7 & JAVA_LOG_LEVEL) << 2;
        }
        $compatibility = chr($compatibility);
        return $compatibility;
    }
    
    $java_initialized = false;

    function __javaproxy_Client_getClient()
    {
        static $client = null;
        if (!is_null($client)) {
            return $client;
        }
        if (function_exists("java_create_client")) {
            
            $client = java_create_client();
        } else {
            
            global $java_initialized;
            
            $client = new Client();
            $java_initialized = true;
        }
        return $client;
    }

    function java_last_exception_get()
    {
        $client = __javaproxy_Client_getClient();
        return $client->invokeMethod(0, "getLastException", array());
    }

    function java_last_exception_clear()
    {
        $client = __javaproxy_Client_getClient();
        $client->invokeMethod(0, "clearLastException", array());
    }

    function java_values_internal($object)
    {
        if (!$object instanceof JavaType) {
            return $object;
        }
        $client = __javaproxy_Client_getClient();
        return $client->invokeMethod(0, "getValues", array($object));
    }

    function java_invoke($object, $method, $args)
    {
        $client = __javaproxy_Client_getClient();
        $id = ($object == null) ? 0 : $object->__java;
        return $client->invokeMethod($id, $method, $args);
    }

    function java_unwrap($object)
    {
        if (!$object instanceof JavaType) {
            throw new Exception\IllegalArgumentException($object);
        }
        $client = __javaproxy_Client_getClient();
        return $client->globalRef->get($client->invokeMethod(0, "unwrapClosure", array($object)));
    }

    function java_values($object)
    {
        return java_values_internal($object);
    }

    /**
     * 
     * @param JavaType $object
     * @return string
     * @throws Exception\IllegalArgumentException
     */
    function java_inspect_internal($object)
    {
        if (!$object instanceof JavaType) {
            throw new Exception\IllegalArgumentException($object);
        }
        $client = __javaproxy_Client_getClient();
        return $client->invokeMethod(0, "inspect", array($object));
    }

    /**
     * 
     * @param JavaType $object
     * @return string
     * @throws Exception\IllegalArgumentException
     */
    function java_inspect($object)
    {
        return java_inspect_internal($object);
    }

    function java_set_file_encoding($enc)
    {
        $client = __javaproxy_Client_getClient();
        return $client->invokeMethod(0, "setFileEncoding", array($enc));
    }


    /**
     * 
     * @param JavaType $ob
     * @param JavaType $clazz
     * @return boolean
     * @throws Exception\IllegalArgumentException
     */
    function java_instanceof_internal($ob, $clazz)
    {
        if (!$ob instanceof JavaType) {
            throw new Exception\IllegalArgumentException($ob);
        }
        if (!$clazz instanceof JavaType) {
            throw new Exception\IllegalArgumentException($clazz);
        }
        $client = __javaproxy_Client_getClient();
        return $client->invokeMethod(0, "instanceOf", array($ob, $clazz));
    }

    /**
     * 
     * @param JavaType $ob
     * @param JavaType $clazz
     * @return boolean
     * @throws Exception\IllegalArgumentException
     */
    function java_instanceof($ob, $clazz)
    {
        return java_instanceof_internal($ob, $clazz);
    }

    /**
     * 
     * @param JavaType $object
     * @param mixed $type
     * @return JavaType
     */
    function java_cast_internal($object, $type)
    {
        if (!$object instanceof JavaType) {
            switch ($type[0]) {
                case 'S': case 's':
                    return (string) $object;
                case 'B': case 'b':
                    return (boolean) $object;
                case 'L': case 'I': case 'l': case 'i':
                    return (integer) $object;
                case 'D': case 'd': case 'F': case 'f':
                    return (float) $object;
                case 'N': case 'n':
                    return null;
                case 'A': case 'a':
                    return (array) $object;
                case 'O': case 'o':
                    return (object) $object;
            }
        }
        return $object->__cast($type);
    }

    function java_cast($object, $type)
    {
        return java_cast_internal($object, $type);
    }

    function java_require($arg)
    {
        trigger_error('java_require() not supported anymore. Please use <a href="http://php-java-bridge.sourceforge.net/pjb/webapp.php>tomcat or jee hot deployment</a> instead', E_USER_WARNING);
    }

    function java_get_lifetime()
    {
        $session_max_lifetime = ini_get("session.gc_maxlifetime");
        return $session_max_lifetime ? (int) $session_max_lifetime : 1440;
    }
    function java_session_array($args)
    {
        $client = __javaproxy_Client_getClient();
        if (!isset($args[0])) {
            $args[0] = null;
        }
        if (!isset($args[1])) {
            $args[1] = 0;
        } elseif ($args[1] === true)
        $args[1] = 1;
        else {
            $args[1] = 2;
        }
        if (!isset($args[2])) {
            $args[2] = java_get_lifetime();
        }
        return $client->getSession($args);
    }

    function java_session()
    {
        return java_session_array(func_get_args());
    }

    function java_server_name()
    {
        try {
            $client = __javaproxy_Client_getClient();
            return $client->getServerName();
        } catch (Exeption\ConnectException $ex) {
            return null;
        }
    }

    function java_context()
    {
        $client = __javaproxy_Client_getClient();
        return $client->getContext();
    }

    function java_closure_array($args)
    {
        if (isset($args[2]) && ((!($args[2] instanceof JavaType)) && !is_array($args[2]))) {
            throw new Exception\IllegalArgumentException($args[2]);
        }
        $client = __javaproxy_Client_getClient();
        $args[0] = isset($args[0]) ? $client->globalRef->add($args[0]) : 0;
        $client->protocol->invokeBegin(0, "makeClosure");
        $n = count($args);
        $client->protocol->writeULong($args[0]);
        for ($i = 1; $i < $n; $i++) {
            $client->writeArg($args[$i]);
        }
        $client->protocol->invokeEnd();
        $val = $client->getResult();
        return $val;
    }

    function java_closure()
    {
        return java_closure_array(func_get_args());
    }

    function java_begin_document()
    {
    }

    function java_end_document()
    {
    }

}

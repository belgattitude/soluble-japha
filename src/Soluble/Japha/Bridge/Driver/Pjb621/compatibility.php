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

namespace Soluble\Japha\Bridge\Driver\Pjb621 {

    use Soluble\Japha\Bridge\Driver\Pjb621\PjbProxyClient;

    /**
     * Kept for compatibilty purpose
     * 
     * @deprecated
     * @return Client
     */
    function __javaproxy_Client_getClient()
    {
        return PjbProxyClient::getInstance()->getClient();
    }

    /**
     * 
     * @deprecated
     * @param mixed $x
     * @return bool
     */
    function java_autoload_function5($x)
    {
        return PjbProxyClient::getInstance()->autoload5($x);
    }

    /**
     * 
     * @deprecated
     * @param mixed $x
     * @return bool
     */
    function java_autoload_function($x)
    {
        return PjbProxyClient::getInstance()->autoload($x);
    }

    /**
     * Return a Java class
     * 
     * @deprecated 
     * @param string $name Name of the java class
     * @return JavaClass
     */
    function java($name)
    {
        return PjbProxyClient::getInstance()->getJavaClass($name);
    }

    /**
     * Invoke a method dynamically.

     * Example:
     * <code>
     * java_invoke(new java("java.lang.String","hello"), "toString", array())
     * </code>
     *
     * <br> Any declared exception can be caught by PHP code. <br>
     * Exceptions derived from java.lang.RuntimeException or Error should
     * not be caught unless declared in the methods throws clause -- OutOfMemoryErrors cannot be caught at all,
     * even if declared.
     *
     * @deprecated
     * @param JavaType $object A java object or type
     * @param string $method A method string
     * @param array $args An argument array
     */
    function java_invoke($object, $method, $args)
    {
        PjbProxyClient::getInstance()->invokeMethod($object, $method, $args);
    }

    /**
     * 
     * @deprectated
     * @param Client $client
     * @return string
     */
    function java_getCompatibilityOption($client)
    {
        return PjbProxyClient::getInstance()->getCompatibilityOption($client);
    }

    /**
     *
     * @param JavaType $ob
     * @param JavaType $clazz
     * @return boolean
     */
    function java_instanceof_internal(JavaType $ob, JavaType $clazz)
    {
        return PjbProxyClient::getInstance()->isInstanceOf($ob, $clazz);
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
        return PjbProxyClient::getInstance()->isInstanceOf($ob, $clazz);
    }

    /**
     * Evaluate a Java object.
     *
     * Evaluate a object and fetch its content, if possible. Use java_values() to convert a Java object into an equivalent PHP value.
     *
     * A java array, Map or Collection object is returned
     * as a php array. An array, Map or Collection proxy is returned as a java array, Map or Collection object, and a null proxy is returned as null. All values of java types for which a primitive php type exists are returned as php values. Everything else is returned unevaluated. Please make sure that the values do not not exceed
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
     * @deprecated
     * @see java_closure()
     * @param JavaType $object
     */
    function java_values(JavaType $object)
    {
        return PjbProxyClient::getInstance()->getValues($object);
    }

    /**
     * 
     * @deprecated
     * @param JavaType $object
     */
    function java_values_internal($object)
    {
        return PjbProxyClient::getInstance()->getValues($object);
    }

    /**
     *
     * @deprecated
     * @param JavaType $object
     * @return string
     */
    function java_inspect_internal(JavaType $object)
    {
        //$client = __javaproxy_Client_getClient();
        //return $client->invokeMethod(0, "inspect", array($object));
        return PjbProxyClient::getInstance()->inspect($object);
    }

    /**
     *
     * @deprecated
     * @param JavaType $object
     * @return string
     * @throws Exception\IllegalArgumentException
     */
    function java_inspect(JavaType $object)
    {
        return PjbProxyClient::getInstance()->inspect($object);
        //return java_inspect_internal($object);
    }
    
    /**
     * 
     * @deprecated
     */
    function java_last_exception_get()
    {
        return PjbProxyClient::getInstance()->getLastException();
    }

    /**
     * @deprecated
     */
    function java_last_exception_clear()
    {
        return PjbProxyClient::getInstance()->clearLastException();
    }

    /**
     * 
     * @return string
     */
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
            } elseif (is_string($kontinuation)) {
                java_context()->call(call_user_func($kontinuation));
            } elseif ($kontinuation instanceof JavaType) {
                java_context()->call($kontinuation);
            } else {
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

    /**
     * Unwrap a Java object.
     *
     * Fetches the PHP object which has been wrapped by java_closure(). Example:
     * <code>
     * class foo { function __toString() {return "php"; } function toString() {return "java";} }
     * $foo = java_closure(new foo());
     * echo $foo;
     * => java;
     * $foo = java_unwrap($foo);
     * echo $foo;
     * => php
     * </code>
     * @param JavaType $object
     */
    function java_unwrap(JavaType $object)
    {
        $client = __javaproxy_Client_getClient();
        return $client->globalRef->get($client->invokeMethod(0, "unwrapClosure", array($object)));
    }

    function java_set_file_encoding($enc)
    {
        $client = __javaproxy_Client_getClient();
        return $client->invokeMethod(0, "setFileEncoding", array($enc));
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
        } elseif ($args[1] === true) {
            $args[1] = 1;
        } else {
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

    /**
     *
     * @return string|null
     */
    function java_server_name()
    {
        try {
            $client = __javaproxy_Client_getClient();
            return $client->getServerName();
        } catch (Exception\ConnectException $ex) {
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

    /*
     * Can be removed
     * 
     */
    /*
     * 
      function java_require($arg)
      {
      trigger_error('java_require() not supported anymore. Please use <a href="http://php-java-bridge.sourceforge.net/pjb/webapp.php>tomcat or jee hot deployment</a> instead', E_USER_WARNING);
      }

      function java_autoload($libs = null)
      {
      trigger_error('Please use <a href="http://php-java-bridge.sourceforge.net/pjb/webapp.php>tomcat or jee hot deployment</a> instead', E_USER_WARNING);
      }
      function java_begin_document()
      {
      }

      function java_end_document()
      {
      }
      function bootstrap()
      {
      }


     */



    // REFACTORED METHODS
    /*
      function __javaproxy_Client_getClient2()
      {
      static $client = null;
      Soluble\Japha\Bridge\Driver\Pjb621::$client;
      if (!is_null($client)) {
      return $client;
      }
      if (function_exists("java_create_client")) {
      $client = java_create_client();
      } else {
      global $java_initialized;
      $client = new Client();

      $client->throwExceptionProxyFactory =
      new Adapter\DefaultThrowExceptionProxyFactory($client);

      $java_initialized = true;
      }
      return $client;
      }
     */
}

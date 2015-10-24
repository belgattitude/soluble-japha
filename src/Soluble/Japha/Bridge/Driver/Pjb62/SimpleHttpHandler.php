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

namespace Soluble\Japha\Bridge\Driver\Pjb62;

class SimpleHttpHandler extends SocketHandler
{
    public $headers;
    public $cookies;
    public $context;
    public $ssl;

    /**
     * @var integer
     */
    public $port;

    /**
     * @var string
     */
    public $host;

    /**
     *
     * @param Protocol $protocol
     * @param string $ssl
     * @param string $host
     * @param integer $port
     */
    public function __construct(Protocol $protocol, $ssl, $host, $port)
    {
        $this->cookies = array();
        $this->protocol = $protocol;
        $this->ssl = $ssl;
        $this->host = $host;
        $this->port = $port;
        $this->createChannel();
    }

    public function createChannel()
    {
        $channelName = java_getHeader("X_JAVABRIDGE_REDIRECT", $_SERVER);
        $context = java_getHeader("X_JAVABRIDGE_CONTEXT", $_SERVER);
        $len = strlen($context);
        $len0 = java_getCompatibilityOption($this->protocol->client);
        $len1 = chr($len & 0xFF);
        $len>>=8;
        $len2 = chr($len & 0xFF);
        $this->channel = new EmptyChannel($this);
        $this->channel = $this->getChannel($channelName);
        $this->protocol->setSocketHandler(new SocketHandler($this->protocol, $this->channel));
        $this->protocol->write("\177${len0}${len1}${len2}${context}");
        $this->context = sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n", $context);
        $this->protocol->handler = $this->protocol->getSocketHandler();
        $this->protocol->handler->write($this->protocol->client->sendBuffer)
                or $this->protocol->handler->shutdownBrokenConnection("Broken local connection handle");
        $this->protocol->client->sendBuffer = null;
        $this->protocol->handler->read(1)
                or $this->protocol->handler->shutdownBrokenConnection("Broken local connection handle");
    }

    /**
     *
     * @return string
     */
    public function getCookies()
    {
        $str = "";
        $first = true;
        foreach ($_COOKIE as $k => $v) {
            $str .=($first ? "Cookie: $k=$v" : "; $k=$v");
            $first = false;
        }
        if (!$first) {
            $str .="\r\n";
        }
        return $str;
    }

    /**
     *
     * @return string
     */
    public function getContextFromCgiEnvironment()
    {
        $ctx = java_getHeader('X_JAVABRIDGE_CONTEXT', $_SERVER);
        return $ctx;
    }

    public function getContext()
    {
        static $context = null;
        if ($context) {
            return $context;
        }
        $ctx = $this->getContextFromCgiEnvironment();
        $context = "";
        if ($ctx) {
            $context = sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n", $ctx);
        }
        return $context;
    }

    public function getWebAppInternal()
    {
        $context = $this->protocol->webContext;
        if (isset($context)) {
            return $context;
        }
        return (JAVA_SERVLET == "User" &&
                array_key_exists('PHP_SELF', $_SERVER) &&
                array_key_exists('HTTP_HOST', $_SERVER)) ? $_SERVER['PHP_SELF'] . "javabridge" : null;
    }

    public function getWebApp()
    {
        $context = $this->getWebAppInternal();
        if (is_null($context)) {
            $context = JAVA_SERVLET;
        }
        if (is_null($context) || $context[0] != "/") {
            $context = "/JavaBridge/JavaBridge.phpjavabridge";
        }
        return $context;
    }

    public function write($data)
    {
        return $this->protocol->getSocketHandler()->write($data);
    }

    public function doSetCookie($key, $val, $path)
    {
        $path = trim($path);
        $webapp = $this->getWebAppInternal();
        if (!$webapp) {
            $path = "/";
        }
        setcookie($key, $val, 0, $path);
    }

    /**
     * 
     * @param integer $size
     * @return string
     */
    public function read($size)
    {
        return $this->protocol->getSocketHandler()->read($size);
    }

    /**
     * 
     * @param string $channelName
     * @return SocketChannelP
     * @throws Exception\IllegalStateException
     */
    public function getChannel($channelName)
    {
        $errstr = null;
        $errno = null;
        $peer = pfsockopen($this->host, $channelName, $errno, $errstr, 20);
        if (!$peer) {
            throw new Exception\IllegalStateException("No ContextServer for {$this->host}:{$channelName}. Error: $errstr ($errno)\n");
        }
        stream_set_timeout($peer, -1);
        return new SocketChannelP($peer, $this->host);
    }

    public function keepAlive()
    {
        parent::keepAlive();
    }

    public function redirect()
    {
    }
}

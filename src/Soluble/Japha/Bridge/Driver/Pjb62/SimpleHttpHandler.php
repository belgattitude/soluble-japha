<?php

/**
 * soluble-japha / PHPJavaBridge driver client.
 *
 * Refactored version of phpjababridge's Java.inc file compatible
 * with php java bridge 6.2
 *
 *
 * @credits   http://php-java-bridge.sourceforge.net/pjb/
 *
 * @see      http://github.com/belgattitude/soluble-japha
 *
 * @author Jost Boekemeier
 * @author Vanvelthem Sébastien (refactoring and fixes from original implementation)
 * @license   MIT
 *
 * The MIT License (MIT)
 * Copyright (c) 2014-2017 Jost Boekemeier
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
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

class SimpleHttpHandler extends SocketHandler
{
    public $headers;
    public $cookies;
    public $context;
    public $ssl;

    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $host;

    /**
     * @var array
     */
    protected $cachedValues = [];

    /**
     * @var string
     */
    protected $java_servlet;

    /**
     * @var int
     */
    protected $java_recv_size;

    /**
     * @var int
     */
    protected $java_send_size;

    /**
     * @param Protocol $protocol
     * @param string   $ssl
     * @param string   $host
     * @param int      $port
     * @param string   $java_servlet
     * @param int      $java_recv_size
     * @param int      $java_send_size
     */
    public function __construct(Protocol $protocol, $ssl, $host, $port, $java_servlet, $java_recv_size, $java_send_size)
    {
        $this->cookies = [];
        $this->protocol = $protocol;
        $this->ssl = $ssl;
        $this->host = $host;
        $this->port = $port;
        $this->java_servlet = $java_servlet;

        $this->java_send_size = $java_send_size;
        $this->java_recv_size = $java_recv_size;

        $this->cachedValues = [
            'getContext' => null
        ];
        $this->createChannel();
    }

    public function createChannel()
    {
        $channelName = Pjb62Driver::getJavaBridgeHeader('X_JAVABRIDGE_REDIRECT', $_SERVER);
        $context = Pjb62Driver::getJavaBridgeHeader('X_JAVABRIDGE_CONTEXT', $_SERVER);
        $len = strlen($context);
        $len0 = PjbProxyClient::getInstance()->getCompatibilityOption($this->protocol->client);
        $len1 = chr($len & 0xFF);
        $len >>= 8;
        $len2 = chr($len & 0xFF);
        $this->channel = new EmptyChannel($this, $this->java_recv_size, $this->java_send_size);
        $this->channel = $this->getChannel($channelName);
        $this->protocol->setSocketHandler(new SocketHandler($this->protocol, $this->channel));
        $this->protocol->write("\177${len0}${len1}${len2}${context}");
        $this->context = sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n", $context);
        $this->protocol->handler = $this->protocol->getSocketHandler();
        $this->protocol->handler->write($this->protocol->client->sendBuffer)
                or $this->protocol->handler->shutdownBrokenConnection('Broken local connection handle');
        $this->protocol->client->sendBuffer = null;
        $this->protocol->handler->read(1)
                or $this->protocol->handler->shutdownBrokenConnection('Broken local connection handle');
    }

    /**
     * @return string
     */
    public function getContextFromCgiEnvironment()
    {
        $ctx = Pjb62Driver::getJavaBridgeHeader('X_JAVABRIDGE_CONTEXT', $_SERVER);

        return $ctx;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        if ($this->cachedValues['getContext'] === null) {
            $ctx = $this->getContextFromCgiEnvironment();
            $context = '';
            if ($ctx) {
                $context = sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n", $ctx);
            }
            $this->cachedValues['getContext'] = $context;
        }

        return $this->cachedValues['getContext'];
    }

    public function getWebAppInternal()
    {
        $context = $this->protocol->webContext;
        if (isset($context)) {
            return $context;
        }

        return ($this->java_servlet == 'User' &&
                array_key_exists('PHP_SELF', $_SERVER) &&
                array_key_exists('HTTP_HOST', $_SERVER)) ? $_SERVER['PHP_SELF'] . 'javabridge' : null;
        /*
        return (JAVA_SERVLET == "User" &&
                array_key_exists('PHP_SELF', $_SERVER) &&
                array_key_exists('HTTP_HOST', $_SERVER)) ? $_SERVER['PHP_SELF'] . "javabridge" : null;
         *
         *
         */
    }

    public function getWebApp()
    {
        $context = $this->getWebAppInternal();
        if (is_null($context)) {
            $context = $this->java_servlet;
        }
        if (is_null($context) || $context[0] != '/') {
            $context = '/JavaBridge/JavaBridge.phpjavabridge';
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
            $path = '/';
        }
        setcookie($key, $val, 0, $path);
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function read($size)
    {
        return $this->protocol->getSocketHandler()->read($size);
    }

    /**
     * @param string $channelName
     *
     * @return SocketChannelP
     *
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

        return new SocketChannelP($peer, $this->host, $this->java_recv_size, $this->java_send_size);
    }

    public function keepAlive()
    {
        parent::keepAlive();
    }

    public function redirect()
    {
    }
}

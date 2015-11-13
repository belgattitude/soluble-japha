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

class Protocol {

    /**
     *
     * @var Client
     */
    public $client;
    public $webContext;

    /**
     *
     * @var string
     */
    public $serverName;

    /**
     *
     * @var SimpleHttpHandler|HttpTunnelHandler|SocketHandler
     */
    public $handler;

    /**
     *
     * @var SocketHandler
     */
    protected $socketHandler;

    /**
     *
     * @var array
     */
    protected $host;

    /**
     *
     * @var string 
     */
    protected $java_hosts;    
    
    /**
     *
     * @var string 
     */
    protected $java_servlet;
    
    /**
     *
     * @var int
     */
    public $java_recv_size;
    
    /**
     *
     * @var int
     */
    public $java_send_size;


    
    /**
     * 
     * @param Client $client
     * @param string $java_hosts
     * @param string $java_servlet
     * @param int $java_recv_size
     * @param int $java_send_size
     */
    public function __construct(Client $client, $java_hosts, $java_servlet, $java_recv_size, $java_send_size) {
        $this->client = $client;
        $this->java_hosts = $java_hosts;
        $this->java_servlet = $java_servlet;
        $this->java_recv_size = $java_recv_size;        
        $this->java_send_size = $java_send_size;
        $this->setHost($java_hosts);
        $this->handler = $this->createHandler();
    }

    /**
     *
     * @return string
     */
    public function getOverrideHosts() {
        if (array_key_exists('X_JAVABRIDGE_OVERRIDE_HOSTS', $_ENV)) {
            $override = $_ENV['X_JAVABRIDGE_OVERRIDE_HOSTS'];
            if (!is_null($override) && $override != '/') {
                return $override;
            }
        }
        return java_getHeader('X_JAVABRIDGE_OVERRIDE_HOSTS_REDIRECT', $_SERVER);
    }

    /**
     *
     * @param SocketHandler $socketHandler
     */
    public function setSocketHandler(SocketHandler $socketHandler) {
        $this->socketHandler = $socketHandler;
    }

    /**
     *
     * @return SocketHandler  socket handler
     */
    public function getSocketHandler() {
        return $this->socketHandler;
    }

    /**
     * 
     * @param string $java_hosts
     */
    public function setHost($java_hosts) {
        $hosts = explode(";", $java_hosts);
        //$hosts = explode(";", JAVA_HOSTS);
        $host = explode(":", $hosts[0]);
        while (count($host) < 3) {
            array_unshift($host, "");
        }
        if (substr($host[1], 0, 2) == "//") {
            $host[1] = substr($host[1], 2);
        }
        $this->host = $host;
    }

    /**
     *
     * @return array
     */
    public function getHost() {
        return $this->host;
    }

    /**
     *
     * @return SimpleHttpHandler|HttpTunnelHandler
     */
    public function createHttpHandler() {
        $overrideHosts = $this->getOverrideHosts();
        $ssl = "";
        if ($overrideHosts) {
            $s = $overrideHosts;
            if ((strlen($s) > 2) && ($s[1] == ':')) {
                if ($s[0] == 's') {
                    $ssl = "ssl://";
                }
                $s = substr($s, 2);
            }
            $webCtx = strpos($s, "//");
            if ($webCtx) {
                $host = substr($s, 0, $webCtx);
            } else {
                $host = $s;
            }
            $idx = strpos($host, ':');
            if ($idx) {
                if ($webCtx) {
                    $port = substr($host, $idx + 1, $webCtx);
                } else {
                    $port = substr($host, $idx + 1);
                }
                $host = substr($host, 0, $idx);
            } else {
                $port = "8080";
            }
            if ($webCtx) {
                $webCtx = substr($s, $webCtx + 1);
            }
            $this->webContext = $webCtx;
        } else {

            $hostVec = $this->getHost();
            if ($ssl = $hostVec[0]) {
                $ssl .="://";
            }
            $host = $hostVec[1];
            $port = $hostVec[2];
        }
        $this->serverName = "${ssl}${host}:$port";
        
        
        if ((array_key_exists("X_JAVABRIDGE_REDIRECT", $_SERVER)) ||
                (array_key_exists("HTTP_X_JAVABRIDGE_REDIRECT", $_SERVER))) {

            return new SimpleHttpHandler($this, $ssl, $host, $port, $this->java_servlet, $this->java_recv_size, $this->java_send_size);
        }

        return new HttpTunnelHandler($this, $ssl, $host, $port, $this->java_servlet, $this->java_recv_size, $this->java_send_size);
    }

    /**
     *
     * @param string $name
     * @param boolean $again
     * @return \Soluble\Japha\Bridge\Driver\Pjb62\SocketHandler
     * @throws Exception\ConnectException
     * @throws Exception\IOException
     */
    public function createSimpleHandler($name, $again = true) {
        $channelName = $name;
        $errno = null;
        $errstr = null;
        if (is_numeric($channelName)) {
            $peer = @pfsockopen($host = "127.0.0.1", $channelName, $errno, $errstr, 5);
        } else {
            $type = $channelName[0];
            list($host, $channelName) = explode(":", $channelName);
            $peer = pfsockopen($host, $channelName, $errno, $errstr, 20);
            if (!$peer) {
                throw new Exception\ConnectException("No Java server at $host:$channelName. Error message: $errstr ($errno)");
            }
        }
        if (!$peer) {
            $java = file_exists(ini_get("extension_dir") . "/JavaBridge.jar") ? ini_get("extension_dir") . "/JavaBridge.jar" : (java_get_base() . "/JavaBridge.jar");
            if (!file_exists($java)) {
                throw new Exception\IOException("Could not find $java in " . getcwd() . ". Download it from http://sf.net/projects/php-java-bridge/files/Binary%20package/php-java-bridge_" . JAVA_PEAR_VERSION . "/exploded/JavaBridge.jar/download and try again.");
            }
            $java_cmd = "java -Dphp.java.bridge.daemon=true -jar \"${java}\" INET_LOCAL:$channelName 0";
            if (!$again) {
                throw new Exception\ConnectException("No Java back end! Please run it with: $java_cmd. Error message: $errstr ($errno)");
            }
            if (!java_checkCliSapi()) {
                trigger_error("This PHP SAPI requires a JEE or SERVLET back end. Start it,define ('JAVA_SERVLET',true); define('JAVA_HOSTS',...); and try again.", E_USER_ERROR);
            }
            system($java_cmd);
            return $this->createSimpleHandler($name, false);
        }
        stream_set_timeout($peer, -1);
        $handler = new SocketHandler($this, new SocketChannelP($peer, $host, $this->java_recv_size, $this->java_send_size));
        //$compatibility = java_getCompatibilityOption($this->client);
        $compatibility = PjbProxyClient::getInstance()->getCompatibilityOption($this->client);
        $this->write("\177$compatibility");
        $this->serverName = "127.0.0.1:$channelName";
        return $handler;
    }

    /**
     *
     * @return string
     */
    public function java_get_simple_channel() {
        $java_hosts = $this->java_hosts;
        $java_servlet = $this->java_servlet;

        return ($java_hosts && (!$java_servlet || ($java_servlet == "Off"))) ? $java_hosts : null;
        //return (JAVA_HOSTS && (!JAVA_SERVLET || (JAVA_SERVLET == "Off"))) ? JAVA_HOSTS : null;
    }

    public function createHandler() {
        if (!java_getHeader('X_JAVABRIDGE_OVERRIDE_HOSTS', $_SERVER) &&
                ((function_exists("java_get_default_channel") && ($defaultChannel = java_get_default_channel())) ||
                ($defaultChannel = $this->java_get_simple_channel()))) {
            return $this->createSimpleHandler($defaultChannel);
        } else {
            return $this->createHttpHandler();
        }
    }

    public function redirect() {
        $this->handler->redirect();
    }

    public function read($size) {
        return $this->handler->read($size);
    }

    public function sendData() {
        $this->handler->write($this->client->sendBuffer);
        $this->client->sendBuffer = null;
    }

    public function flush() {
        $this->sendData();
    }

    public function getKeepAlive() {
        return $this->handler->getKeepAlive();
    }

    public function keepAlive() {
        $this->handler->keepAlive();
    }

    public function handle() {
        $this->client->handleRequests();
    }

    public function write($data) {
        $this->client->sendBuffer.=$data;
    }

    public function finish() {
        $this->flush();
        $this->handle();
        $this->redirect();
    }

    /*
     * @param string $name java class name, i.e java.math.BigInteger
     */

    public function referenceBegin($name) {
        $this->client->sendBuffer.=$this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $signature = sprintf("<H p=\"1\" v=\"%s\">", $name);
        $this->write($signature);
        $signature[6] = "2";
        $this->client->currentArgumentsFormat = $signature;
    }

    public function referenceEnd() {
        $this->client->currentArgumentsFormat.=$format = "</H>";
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    /**
     *
     * @param string $name java class name i.e java.math.BigInteger
     */
    public function createObjectBegin($name) {
        $this->client->sendBuffer.=$this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $signature = sprintf("<K p=\"1\" v=\"%s\">", $name);
        $this->write($signature);
        $signature[6] = "2";
        $this->client->currentArgumentsFormat = $signature;
    }

    public function createObjectEnd() {
        $this->client->currentArgumentsFormat.=$format = "</K>";
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    /**
     *
     * @param integer $object object id
     * @param string $method method name
     */
    public function propertyAccessBegin($object, $method) {
        $this->client->sendBuffer.=$this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf("<G p=\"1\" v=\"%x\" m=\"%s\">", $object, $method));
        $this->client->currentArgumentsFormat = "<G p=\"2\" v=\"%x\" m=\"${method}\">";
    }

    public function propertyAccessEnd() {
        $this->client->currentArgumentsFormat.=$format = "</G>";
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    /**
     *
     * @param integer $object object id
     * @param string $method method name
     */
    public function invokeBegin($object, $method) {
        $this->client->sendBuffer.=$this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf("<Y p=\"1\" v=\"%x\" m=\"%s\">", $object, $method));
        $this->client->currentArgumentsFormat = "<Y p=\"2\" v=\"%x\" m=\"${method}\">";
    }

    public function invokeEnd() {
        $this->client->currentArgumentsFormat.=$format = "</Y>";
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    public function resultBegin() {
        $this->client->sendBuffer.=$this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write("<R>");
    }

    public function resultEnd() {
        $this->client->currentCacheKey = null;
        $this->write("</R>");
        $this->flush();
    }

    /**
     *
     * @param string $name
     */
    public function writeString($name) {
        $this->client->currentArgumentsFormat.=$format = "<S v=\"%s\"/>";
        $this->write(sprintf($format, htmlspecialchars($name, ENT_COMPAT)));
    }

    /**
     *
     * @param boolean $boolean
     */
    public function writeBoolean($boolean) {
        $this->client->currentArgumentsFormat.=$format = "<T v=\"%s\"/>";
        $this->write(sprintf($format, $boolean));
    }

    /**
     *
     * @param integer $l
     */
    public function writeLong($l) {
        $this->client->currentArgumentsFormat.="<J v=\"%d\"/>";
        if ($l < 0) {
            $this->write(sprintf("<L v=\"%x\" p=\"A\"/>", -$l));
        } else {
            $this->write(sprintf("<L v=\"%x\" p=\"O\"/>", $l));
        }
    }

    /**
     *
     * @param integer $l
     */
    public function writeULong($l) {
        $this->client->currentArgumentsFormat.=$format = "<L v=\"%x\" p=\"O\"/>";
        $this->write(sprintf($format, $l));
    }

    /**
     *
     * @param double $d
     */
    public function writeDouble($d) {
        $this->client->currentArgumentsFormat.=$format = "<D v=\"%.14e\"/>";
        $this->write(sprintf($format, $d));
    }

    /**
     *
     * @param integer $object
     */
    public function writeObject($object) {
        $this->client->currentArgumentsFormat.=$format = "<O v=\"%x\"/>";
        $this->write(sprintf($format, $object));
    }

    /**
     *
     * @param integer $object
     * @param string $str
     */
    public function writeException($object, $str) {
        $this->write(sprintf("<E v=\"%x\" m=\"%s\"/>", $object, htmlspecialchars($str, ENT_COMPAT)));
    }

    public function writeCompositeBegin_a() {
        $this->write("<X t=\"A\">");
    }

    public function writeCompositeBegin_h() {
        $this->write("<X t=\"H\">");
    }

    public function writeCompositeEnd() {
        $this->write("</X>");
    }

    /**
     *
     * @param string $key
     */
    public function writePairBegin_s($key) {
        $this->write(sprintf("<P t=\"S\" v=\"%s\">", htmlspecialchars($key, ENT_COMPAT)));
    }

    /**
     *
     * @param integer $key
     */
    public function writePairBegin_n($key) {
        $this->write(sprintf("<P t=\"N\" v=\"%x\">", $key));
    }

    public function writePairBegin() {
        $this->write("<P>");
    }

    public function writePairEnd() {
        $this->write("</P>");
    }

    /**
     *
     * @param integer $object
     */
    public function writeUnref($object) {
        $this->client->sendBuffer.=$this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf("<U v=\"%x\"/>", $object));
    }

    /**
     *
     * @return string
     */
    public function getServerName() {
        return $this->serverName;
    }

}

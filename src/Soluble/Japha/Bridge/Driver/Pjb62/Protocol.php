<?php

declare(strict_types=1);

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

use Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException;
use Soluble\Japha\Bridge\Exception\ConnectionException;

class Protocol
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var string
     */
    public $webContext;

    /**
     * @var string
     */
    public $serverName;

    /**
     * @var SimpleHttpHandler|HttpTunnelHandler|SocketHandler
     */
    public $handler;

    /**
     * @var SocketHandler
     */
    protected $socketHandler;

    /**
     * @var array
     */
    protected $host;

    /**
     * @var string
     */
    protected $java_hosts;

    /**
     * @var string
     */
    protected $java_servlet;

    /**
     * @var int
     */
    public $java_recv_size;

    /**
     * @var int
     */
    public $java_send_size;

    /**
     * @var string
     */
    protected $internal_encoding;

    /**
     * @param Client $client
     * @param string $java_hosts
     * @param string $java_servlet
     * @param int    $java_recv_size
     * @param int    $java_send_size
     */
    public function __construct(Client $client, $java_hosts, $java_servlet, $java_recv_size, $java_send_size)
    {
        $this->client = $client;
        $this->internal_encoding = $client->getParam(Client::PARAM_JAVA_INTERNAL_ENCODING);
        $this->java_hosts = $java_hosts;
        $this->java_servlet = $java_servlet;
        $this->java_recv_size = $java_recv_size;
        $this->java_send_size = $java_send_size;
        $this->setHost($java_hosts);
        $this->handler = $this->createHandler();
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getOverrideHosts(): string
    {
        if (array_key_exists('X_JAVABRIDGE_OVERRIDE_HOSTS', $_ENV)) {
            $override = $_ENV['X_JAVABRIDGE_OVERRIDE_HOSTS'];
            if (null !== $override && $override !== '/') {
                return $override;
            }
        }

        return Pjb62Driver::getJavaBridgeHeader('X_JAVABRIDGE_OVERRIDE_HOSTS_REDIRECT', $_SERVER);
    }

    /**
     * @param SocketHandler $socketHandler
     */
    public function setSocketHandler(SocketHandler $socketHandler): void
    {
        $this->socketHandler = $socketHandler;
    }

    public function getSocketHandler(): SocketHandler
    {
        if ($this->socketHandler === null) {
            throw new BrokenConnectionException('No SocketHandler defined');
        }

        return $this->socketHandler;
    }

    /**
     * @param string $java_hosts
     */
    public function setHost(string $java_hosts): void
    {
        $hosts = explode(';', $java_hosts);
        //$hosts = explode(";", JAVA_HOSTS);
        $host = explode(':', $hosts[0]);
        while (count($host) < 3) {
            array_unshift($host, '');
        }
        if (substr($host[1], 0, 2) === '//') {
            $host[1] = substr($host[1], 2);
        }
        $this->host = $host;
    }

    /**
     * @return array
     */
    public function getHost(): array
    {
        return $this->host;
    }

    /**
     * @return SimpleHttpHandler|HttpTunnelHandler
     */
    public function createHttpHandler()
    {
        $overrideHosts = $this->getOverrideHosts();
        $ssl = '';
        if ($overrideHosts) {
            $s = $overrideHosts;
            if ((strlen($s) > 2) && ($s[1] === ':')) {
                if ($s[0] === 's') {
                    $ssl = 'ssl://';
                }
                $s = substr($s, 2);
            }
            $webCtx = strpos($s, '//');
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
                $port = '8080';
            }
            if ($webCtx) {
                $webCtx = substr($s, $webCtx + 1);
            }
            $this->webContext = $webCtx;
        } else {
            $hostVec = $this->getHost();
            if ($ssl = $hostVec[0]) {
                $ssl .= '://';
            }
            $host = $hostVec[1];
            $port = $hostVec[2];
        }
        $this->serverName = "${ssl}${host}:$port";

        if ((array_key_exists('X_JAVABRIDGE_REDIRECT', $_SERVER)) ||
                (array_key_exists('HTTP_X_JAVABRIDGE_REDIRECT', $_SERVER))) {
            return new SimpleHttpHandler($this, $ssl, $host, $port, $this->java_servlet, $this->java_recv_size, $this->java_send_size);
        }

        return new HttpTunnelHandler($this, $ssl, $host, $port, $this->java_servlet, $this->java_recv_size, $this->java_send_size);
    }

    /**
     * @throws ConnectionException
     * @throws Exception\IOException
     */
    public function createSimpleHandler(?string $name = ''): SocketHandler
    {
        $channelName = $name;
        $errno = null;
        $errstr = null;
        if (is_numeric($channelName)) {
            $peer = @pfsockopen($host = '127.0.0.1', $channelName, $errno, $errstr, 5);
        } else {
            $type = $channelName[0];
            list($host, $channelName) = explode(':', $channelName);
            $peer = pfsockopen($host, $channelName, $errno, $errstr, 20);
            if (!$peer) {
                throw new ConnectionException("No Java server at $host:$channelName. Error message: $errstr ($errno)");
            }
        }
        stream_set_timeout($peer, -1);
        $handler = new SocketHandler($this, new SocketChannelP($peer, $host, $this->java_recv_size, $this->java_send_size));
        //$compatibility = java_getCompatibilityOption($this->client);
        $compatibility = PjbProxyClient::getInstance()->getCompatibilityOption($this->client);
        $this->write("\177$compatibility");
        $this->serverName = "127.0.0.1:$channelName";

        return $handler;
    }

    public function java_get_simple_channel(): ?string
    {
        $java_hosts = $this->java_hosts;
        $java_servlet = $this->java_servlet;

        return ($java_hosts && (!$java_servlet || ($java_servlet === 'Off'))) ? $java_hosts : null;
        //return (JAVA_HOSTS && (!JAVA_SERVLET || (JAVA_SERVLET == "Off"))) ? JAVA_HOSTS : null;
    }

    public function createHandler()
    {
        if (!Pjb62Driver::getJavaBridgeHeader('X_JAVABRIDGE_OVERRIDE_HOSTS', $_SERVER) &&
                //((function_exists('java_get_default_channel') && ($defaultChannel = java_get_default_channel())) ||
                ($defaultChannel = $this->java_get_simple_channel())) {
            return $this->createSimpleHandler($defaultChannel);
        } else {
            return $this->createHttpHandler();
        }
    }

    public function redirect(): void
    {
        $this->handler->redirect();
    }

    /**
     * @return string
     */
    public function read(int $size): string
    {
        return $this->handler->read($size);
    }

    public function sendData(): void
    {
        $this->handler->write($this->client->sendBuffer);
        $this->client->sendBuffer = null;
    }

    public function flush(): void
    {
        $this->sendData();
    }

    public function getKeepAlive(): string
    {
        return $this->handler->getKeepAlive();
    }

    public function keepAlive(): void
    {
        $this->handler->keepAlive();
    }

    public function handle(): void
    {
        $this->client->handleRequests();
    }

    /**
     * @param string $data
     */
    public function write($data): void
    {
        $this->client->sendBuffer .= $data;
    }

    public function finish(): void
    {
        $this->flush();
        $this->handle();
        $this->redirect();
    }

    /*
     * @param string $name java class name, i.e java.math.BigInteger
     */

    public function referenceBegin($name): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $signature = sprintf('<H p="1" v="%s">', $name);
        $this->write($signature);
        $signature[6] = '2';
        $this->client->currentArgumentsFormat = $signature;
    }

    public function referenceEnd(): void
    {
        $format = '</H>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    /**
     * @param string $name java class name i.e java.math.BigInteger
     */
    public function createObjectBegin($name): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $signature = sprintf('<K p="1" v="%s">', $name);
        $this->write($signature);
        $signature[6] = '2';
        $this->client->currentArgumentsFormat = $signature;
    }

    public function createObjectEnd(): void
    {
        $format = '</K>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    /**
     * @param int    $object object id
     * @param string $method method name
     */
    public function propertyAccessBegin($object, $method): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf('<G p="1" v="%x" m="%s">', $object, $method));
        $this->client->currentArgumentsFormat = "<G p=\"2\" v=\"%x\" m=\"${method}\">";
    }

    public function propertyAccessEnd(): void
    {
        $format = '</G>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    /**
     * @param int    $object_id object id
     * @param string $method    method name
     */
    public function invokeBegin($object_id, $method): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf('<Y p="1" v="%x" m="%s">', $object_id, $method));
        $this->client->currentArgumentsFormat = "<Y p=\"2\" v=\"%x\" m=\"${method}\">";
    }

    public function invokeEnd(): void
    {
        $format = '</Y>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write($format);
        $this->finish();
        $this->client->currentCacheKey = null;
    }

    public function resultBegin(): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write('<R>');
    }

    public function resultEnd(): void
    {
        $this->client->currentCacheKey = null;
        $this->write('</R>');
        $this->flush();
    }

    /**
     * @param string $name
     */
    public function writeString($name): void
    {
        $format = '<S v="%s"/>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write(sprintf($format, htmlspecialchars($name, ENT_COMPAT, $this->internal_encoding)));
    }

    /**
     * @param bool $boolean
     */
    public function writeBoolean($boolean): void
    {
        $format = '<T v="%s"/>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write(sprintf($format, $boolean));
    }

    /**
     * @param int $l
     */
    public function writeLong($l): void
    {
        $this->client->currentArgumentsFormat .= '<J v="%d"/>';
        if ($l < 0) {
            $this->write(sprintf('<L v="%x" p="A"/>', -$l));
        } else {
            $this->write(sprintf('<L v="%x" p="O"/>', $l));
        }
    }

    /**
     * @param mixed $l
     */
    public function writeULong($l): void
    {
        $format = '<L v="%x" p="O"/>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write(sprintf($format, $l));
    }

    /**
     * @param float $d
     */
    public function writeDouble($d): void
    {
        $format = '<D v="%.14e"/>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write(sprintf($format, $d));
    }

    /**
     * @param string|int|null $object
     */
    public function writeObject($object): void
    {
        $format = '<O v="%x"/>';
        $this->client->currentArgumentsFormat .= $format;
        $this->write(sprintf($format, $object));
    }

    /**
     * @param int    $object
     * @param string $str
     */
    public function writeException($object, $str): void
    {
        $this->write(sprintf('<E v="%x" m="%s"/>', $object, htmlspecialchars($str, ENT_COMPAT, $this->internal_encoding)));
    }

    public function writeCompositeBegin_a(): void
    {
        $this->write('<X t="A">');
    }

    public function writeCompositeBegin_h(): void
    {
        $this->write('<X t="H">');
    }

    public function writeCompositeEnd(): void
    {
        $this->write('</X>');
    }

    /**
     * @param string $key
     */
    public function writePairBegin_s($key): void
    {
        $this->write(sprintf('<P t="S" v="%s">', htmlspecialchars($key, ENT_COMPAT, 'ISO-8859-1')));
    }

    /**
     * @param int $key
     */
    public function writePairBegin_n($key): void
    {
        $this->write(sprintf('<P t="N" v="%x">', $key));
    }

    public function writePairBegin(): void
    {
        $this->write('<P>');
    }

    public function writePairEnd(): void
    {
        $this->write('</P>');
    }

    /**
     * @param int $object
     */
    public function writeUnref($object): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf('<U v="%x"/>', $object));
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param int $code
     */
    public function writeExitCode($code): void
    {
        $this->client->sendBuffer .= $this->client->preparedToSendBuffer;
        $this->client->preparedToSendBuffer = null;
        $this->write(sprintf('<Z v="%x"/>', 0xffffffff & $code));
    }
}

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

use Soluble\Japha\Bridge\Exception\ConnectionException;
use Soluble\Japha\Bridge\Http\Cookie;

class SimpleHttpTunnelHandler extends SimpleHttpHandler
{
    /**
     * @var resource
     */
    public $socket;

    /**
     * @var bool
     */
    protected $hasContentLength = false;

    /**
     * @var bool
     */
    protected $isRedirect;

    /**
     * @param Protocol $protocol
     * @param string   $ssl
     * @param string   $host
     * @param int      $port
     * @param string   $java_servlet
     * @param int      $java_recv_size
     * @param int      $java_send_size
     *
     * @throws ConnectionException
     */
    public function __construct($protocol, $ssl, $host, $port, $java_servlet, $java_recv_size, $java_send_size)
    {
        parent::__construct($protocol, $ssl, $host, $port, $java_servlet, $java_recv_size, $java_send_size);
        $this->open();
    }

    public function createSimpleChannel()
    {
        $this->channel = new EmptyChannel($this, $this->java_recv_size, $this->java_send_size);
    }

    public function createChannel()
    {
        $this->createSimpleChannel();
    }

    /**
     * @param string|null $msg
     */
    public function shutdownBrokenConnection($msg = '')
    {
        fclose($this->socket);
        $this->dieWithBrokenConnection($msg);
    }

    /**
     * @param resource    $socket
     * @param int|null    $errno
     * @param string|null $errstr
     *
     * @throws ConnectionException
     */
    protected function checkSocket($socket, $errno = null, $errstr = null)
    {
        if (!$socket) {
            $msg = "Could not connect to the JEE server {$this->ssl}{$this->host}:{$this->port}. Please start it.";
            if ($errstr !== null || $errno !== null) {
                $msg .= '(errno:' . $errno . ',' . $errstr . ')';
            }
            $logger = $this->protocol->getClient()->getLogger();
            $logger->critical("[soluble-japha] $msg." . __METHOD__);
            throw new ConnectionException(__METHOD__ . ' ' . $msg);
        }
    }

    /**
     * @throws ConnectionException
     */
    protected function open()
    {
        $errno = null;
        $errstr = null;

        $location = $this->ssl . $this->host;
        $socket = @fsockopen($location, $this->port, $errno, $errstr, 20);
        $this->checkSocket($socket, $errno, $errstr);
        stream_set_timeout($socket, -1);
        $this->socket = $socket;
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function fread($size)
    {
        $length = hexdec(fgets($this->socket, $this->java_recv_size));
        $data = '';
        while ($length > 0) {
            $str = fread($this->socket, $length);
            if (feof($this->socket)) {
                return;
            }
            $length -= strlen($str);
            $data .= $str;
        }
        fgets($this->socket, 3);

        return $data;
    }

    public function fwrite($data)
    {
        $len = dechex(strlen($data));

        return fwrite($this->socket, "${len}\r\n${data}\r\n");
    }

    protected function close()
    {
        fwrite($this->socket, "0\r\n\r\n");
        fgets($this->socket, $this->java_recv_size);
        fgets($this->socket, 3);
        fclose($this->socket);
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function read($size)
    {
        if (is_null($this->headers)) {
            $this->parseHeaders();
        }
        if (isset($this->headers['http_error'])) {
            if (isset($this->headers['transfer_chunked'])) {
                $str = $this->fread($this->java_recv_size);
            } elseif (isset($this->headers['content_length'])) {
                $len = $this->headers['content_length'];
                for ($str = fread($this->socket, $len); strlen($str) < $len; $str .= fread($this->socket, $len - strlen($str))) {
                    if (feof($this->socket)) {
                        break;
                    }
                }
            } else {
                $str = fread($this->socket, $this->java_recv_size);
            }
            $this->shutdownBrokenConnection($str);
        }

        return $this->fread($this->java_recv_size);
    }

    protected function getBodyFor($compat, $data)
    {
        $len = dechex(2 + strlen($data));

        return "Cache-Control: no-cache\r\nPragma: no-cache\r\nTransfer-Encoding: chunked\r\n\r\n${len}\r\n\177${compat}${data}\r\n";
    }

    public function write($data)
    {
        $compat = PjbProxyClient::getInstance()->getCompatibilityOption($this->protocol->client);
        $this->headers = null;
        $socket = $this->socket;
        $webapp = $this->getWebApp();
        $cookies = Cookie::getCookiesHeaderLine();
        $context = $this->getContext();
        $res = 'PUT ';
        $res .= $webapp;
        $res .= " HTTP/1.1\r\n";
        $res .= "Host: {$this->host}:{$this->port}\r\n";
        $res .= $context;
        $res .= $cookies;
        $res .= $this->getBodyFor($compat, $data);
        $count = fwrite($socket, $res);
        if ($count === false) {
            $this->shutdownBrokenConnection('Cannot write to socket, broken connection handle');
        }
        $flushed = fflush($socket);
        if ($flushed === false) {
            $this->shutdownBrokenConnection('Cannot flush to socket, broken connection handle');
        }

        return $count;
    }

    protected function parseHeaders()
    {
        $this->headers = [];

        $line = trim(fgets($this->socket, $this->java_recv_size));
        $ar = explode(' ', $line);
        $code = ((int) $ar[1]);
        if ($code != 200) {
            $this->headers['http_error'] = $code;
        }
        while (($str = trim(fgets($this->socket, $this->java_recv_size)))) {
            if ($str[0] == 'X') {
                if (!strncasecmp('X_JAVABRIDGE_REDIRECT', $str, 21)) {
                    $this->headers['redirect'] = trim(substr($str, 22));
                } elseif (!strncasecmp('X_JAVABRIDGE_CONTEXT', $str, 20)) {
                    $this->headers['context'] = trim(substr($str, 21));
                }
            } elseif ($str[0] == 'S') {
                if (!strncasecmp('SET-COOKIE', $str, 10)) {
                    $str = substr($str, 12);
                    $this->cookies[] = $str;
                    $ar = explode(';', $str);
                    $cookie = explode('=', $ar[0]);
                    $path = '';
                    if (isset($ar[1])) {
                        $p = explode('=', $ar[1]);
                    }
                    if (isset($p)) {
                        $path = $p[1];
                    }
                    $this->doSetCookie($cookie[0], $cookie[1], $path);
                }
            } elseif ($str[0] == 'C') {
                if (!strncasecmp('CONTENT-LENGTH', $str, 14)) {
                    $this->headers['content_length'] = trim(substr($str, 15));
                    $this->hasContentLength = true;
                } elseif (!strncasecmp('CONNECTION', $str, 10) && !strncasecmp('close', trim(substr($str, 11)), 5)) {
                    $this->headers['connection_close'] = true;
                }
            } elseif ($str[0] == 'T') {
                if (!strncasecmp('TRANSFER-ENCODING', $str, 17) && !strncasecmp('chunked', trim(substr($str, 18)), 7)) {
                    $this->headers['transfer_chunked'] = true;
                }
            }
        }
    }

    /**
     * @return ChunkedSocketChannel
     */
    protected function getSimpleChannel()
    {
        //public function __construct($peer, $host, $recv_size, $send_size)

        // Originally found in Pjb - no sense
        //return new ChunkedSocketChannel($this->socket, $this->protocol, $this->host);
        return new ChunkedSocketChannel($this->socket, $this->host, $this->java_recv_size, $this->java_send_size);
    }

    public function redirect()
    {
        $this->isRedirect = isset($this->headers['redirect']);
        if ($this->isRedirect) {
            $channelName = $this->headers['redirect'];
        } else {
            $channelName = null;
        }
        $context = $this->headers['context'];
        $len = strlen($context);
        $len0 = chr(0xFF);
        $len1 = chr($len & 0xFF);
        $len >>= 8;
        $len2 = chr($len & 0xFF);
        if ($this->isRedirect) {
            $this->protocol->setSocketHandler(new SocketHandler($this->protocol, $this->getChannel($channelName)));
            $this->protocol->write("\177${len0}${len1}${len2}${context}");
            $this->context = sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n", $context);
            $this->close();
            $this->protocol->handler = $this->protocol->getSocketHandler();
            $this->protocol->handler->write($this->protocol->client->sendBuffer)
                    or $this->protocol->handler->shutdownBrokenConnection('Broken local connection handle');
            $this->protocol->client->sendBuffer = null;
            $this->protocol->handler->read(1)
                    or $this->protocol->handler->shutdownBrokenConnection('Broken local connection handle');
        } else {
            $this->protocol->setSocketHandler(new SocketHandler($this->protocol, $this->getSimpleChannel()));
            $this->protocol->handler = $this->protocol->getSocketHandler();
        }
    }
}

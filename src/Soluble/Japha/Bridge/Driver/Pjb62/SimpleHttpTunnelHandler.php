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
use Soluble\Japha\Bridge\Socket\StreamSocket;

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
     * @var string
     */
    protected $httpHeadersPayload;

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
    public function __construct(Protocol $protocol, $ssl, $host, $port, $java_servlet, $java_recv_size, $java_send_size)
    {
        parent::__construct($protocol, $ssl, $host, $port, $java_servlet, $java_recv_size, $java_send_size);
        $this->open();
        $this->httpHeadersPayload = $this->getHttpHeadersPayload();
    }

    public function createSimpleChannel()
    {
        $this->channel = new EmptyChannel($this, $this->java_recv_size, $this->java_send_size);
    }

    public function createChannel()
    {
        $this->createSimpleChannel();
    }

    public function shutdownBrokenConnection(string $msg = '', int $code = null): void
    {
        if (is_resource($this->socket)) {
            fflush($this->socket);
            fclose($this->socket);
        }
        PjbProxyClient::unregisterAndThrowBrokenConnectionException($msg, $code);
    }

    /**
     * @throws ConnectionException
     */
    protected function open()
    {
        try {
            $persistent = $this->protocol->client->getParam(Client::PARAM_USE_PERSISTENT_CONNECTION);
            $streamSocket = new StreamSocket(
                $this->ssl === 'ssl://' ? StreamSocket::TRANSPORT_SSL : StreamSocket::TRANSPORT_TCP,
                $this->host.':'.$this->port,
                null,
                StreamSocket::DEFAULT_CONTEXT,
                $persistent
            );
            $socket = $streamSocket->getSocket();
        } catch (\Throwable $e) {
            $logger = $this->protocol->getClient()->getLogger();
            $logger->critical(sprintf(
                '[soluble-japha] %s (%s)',
                $e->getMessage(),
                __METHOD__
            ));
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }
        stream_set_timeout($socket, -1);
        $this->socket = $socket;
    }

    public function fread(int $size): ?string
    {
        $length = hexdec(fgets($this->socket, $this->java_recv_size));
        $data = '';
        while ($length > 0) {
            $str = fread($this->socket, $length);
            if (feof($this->socket) || $str === false) {
                return null;
            }
            $length -= strlen($str);
            $data .= $str;
        }
        fgets($this->socket, 3);

        return $data;
    }

    public function fwrite(string $data): ?int
    {
        $len = dechex(strlen($data));
        $written = fwrite($this->socket, "${len}\r\n${data}\r\n");
        if ($written === false) {
            return null;
        }

        return $written;
    }

    protected function close(): void
    {
        fwrite($this->socket, "0\r\n\r\n");
        fgets($this->socket, $this->java_recv_size);
        fgets($this->socket, 3);
        fclose($this->socket);
    }

    public function read(int $size): string
    {
        if (null === $this->headers) {
            $this->parseHeaders();
        }

        $http_error = $this->headers['http_error'] ?? null;

        if ($http_error !== null) {
            $str = null;
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
            $str = ($str === false || $str === null) ? '' : $str;

            if ($http_error === 401) {
                $this->shutdownBrokenConnection('Authentication exception', 401);
            } else {
                $this->shutdownBrokenConnection($str);
            }
        }

        $response = $this->fread($this->java_recv_size);
        if ($response === null) {
            $this->shutdownBrokenConnection('Cannot socket read response from SimpleHttpTunnelHandler');
        }

        return (string) $response;
    }

    protected function getBodyFor($compat, $data): string
    {
        $length = dechex(2 + strlen($data));

        return "\r\n${length}\r\n\177${compat}${data}\r\n";
    }

    protected function getHttpHeadersPayload(): string
    {
        $headers = [
            "PUT {$this->getWebApp()} HTTP/1.1",
            "Host: {$this->host}:{$this->port}",
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Transfer-Encoding: chunked',
        ];

        if (($cookieHeaderLine = Cookie::getCookiesHeaderLine()) !== null) {
            $headers[] = $cookieHeaderLine;
        }

        if (($context = trim($this->getContext())) !== '') {
            $headers[] = $context;
        }

        $client = $this->protocol->getClient();
        if (($user = $client->getParam(Client::PARAM_JAVA_AUTH_USER)) !== null) {
            $password = $client->getParam(Client::PARAM_JAVA_AUTH_PASSWORD);
            $encoded_credentials = base64_encode("{$user}:{$password}");
            $headers[] = "Authorization: Basic {$encoded_credentials}";
        }

        return implode("\r\n", $headers);
    }

    public function write(string $data): ?int
    {
        $compat = PjbProxyClient::getInstance()->getCompatibilityOption($this->protocol->client);
        $this->headers = null; // reset headers

        $request = $this->httpHeadersPayload."\r\n".$this->getBodyFor($compat, $data);

        $count = @fwrite($this->socket, $request);
        if ($count === false) {
            $this->shutdownBrokenConnection(
                sprintf(
                    'Cannot write to socket, broken connection handle: %s',
                    json_encode(error_get_last())
                )
            );
        }
        $flushed = @fflush($this->socket);
        if ($flushed === false) {
            $this->shutdownBrokenConnection(
                sprintf(
                    'Cannot flush to socket, broken connection handle: %s',
                    json_encode(error_get_last())
                )
            );
        }

        return (int) $count;
    }

    protected function parseHeaders(): void
    {
        $this->headers = [];

        $res = @fgets($this->socket, $this->java_recv_size);
        if ($res === false) {
            $this->shutdownBrokenConnection('Cannot parse headers, socket cannot be read.');
        }
        $line = trim($res);
        $ar = explode(' ', $line);
        $code = ((int) $ar[1]);
        if ($code !== 200) {
            $this->headers['http_error'] = $code;
        }
        while ($str = trim(fgets($this->socket, $this->java_recv_size))) {
            if ($str[0] === 'X') {
                if (!strncasecmp('X_JAVABRIDGE_REDIRECT', $str, 21)) {
                    $this->headers['redirect'] = trim(substr($str, 22));
                } elseif (!strncasecmp('X_JAVABRIDGE_CONTEXT', $str, 20)) {
                    $this->headers['context'] = trim(substr($str, 21));
                }
            } elseif ($str[0] === 'S') {
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
            } elseif ($str[0] === 'C') {
                if (!strncasecmp('CONTENT-LENGTH', $str, 14)) {
                    $this->headers['content_length'] = trim(substr($str, 15));
                    $this->hasContentLength = true;
                } elseif (!strncasecmp('CONNECTION', $str, 10) && !strncasecmp('close', trim(substr($str, 11)), 5)) {
                    $this->headers['connection_close'] = true;
                }
            } elseif ($str[0] === 'T') {
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
        return new ChunkedSocketChannel($this->socket, $this->host, $this->java_recv_size, $this->java_send_size);
    }

    public function redirect(): void
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
            if ($this->protocol->client->sendBuffer !== null) {
                $written = $this->protocol->handler->write($this->protocol->client->sendBuffer);
                if ($written === null) {
                    $this->protocol->handler->shutdownBrokenConnection('Broken local connection handle');
                }
                $this->protocol->client->sendBuffer = null;
                $read = $this->protocol->handler->read(1);
            }
        } else {
            $this->protocol->setSocketHandler(new SocketHandler($this->protocol, $this->getSimpleChannel()));
            $this->protocol->handler = $this->protocol->getSocketHandler();
        }
    }
}

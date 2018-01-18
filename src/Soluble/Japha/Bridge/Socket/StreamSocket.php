<?php

declare(strict_types=1);

namespace Soluble\Japha\Bridge\Socket;

use Soluble\Japha\Bridge\Exception\ConnectionException;
use Soluble\Japha\Bridge\Exception\InvalidArgumentException;

class StreamSocket implements StreamSocketInterface
{
    public const TRANSPORT_SSL = 'ssl';
    public const TRANSPORT_TCP = 'tcp';

    public const REGISTERED_TRANSPORTS = [
        self::TRANSPORT_SSL,
        self::TRANSPORT_TCP
    ];

    public const DEFAULT_CONTEXT = [
        'http' => [
            'protocol_version' => '1.1',
        ]
    ];

    public const DEFAULT_CONNECT_TIMEOUT = 5.0;

    /**
     * @var resource
     */
    protected $socket;

    protected $persistent = false;
    protected $address;
    protected $connectTimeout;
    protected $streamContext;
    protected $transport;

    /**
     * @param string  $transport      tcp, ssl... see self::TRANSPORT
     * @param string  $address        ip:port
     * @param float   $connectTimeout connection timeout in seconds (float)
     * @param mixed[] $streamContext  see stream_context_create()
     * @param bool    $persistent     whether to use persistent connections
     */
    public function __construct(
        string $transport,
                                string $address,
                                float $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT,
                                array $streamContext = self::DEFAULT_CONTEXT,
                                bool $persistent = false
    ) {
        $this->setTransport($transport);
        $this->address = $address;
        $this->connectTimeout = $connectTimeout;
        $this->streamContext = $streamContext;
        $this->persistent = $persistent;
        $this->createSocket();
    }

    /**
     * @throws InvalidArgumentException when getting an unsupported transport
     */
    protected function setTransport(string $transport)
    {
        if (!in_array($transport, self::REGISTERED_TRANSPORTS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported transport "%s" given (supported: %s)',
                $transport,
                implode(',', array_keys(self::REGISTERED_TRANSPORTS))
            ));
        }
        $this->transport = $transport;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    /**
     * @return resource php socket
     */
    public function getSocket()
    {
        return $this->socket;
    }

    public function getStreamAddress(): string
    {
        return sprintf('%s://%s', $this->getTransport(), $this->address);
    }

    /**
     * @throws ConnectionException
     */
    protected function createSocket(): void
    {
        $this->socket = @stream_socket_client(
            $this->getStreamAddress(),
            $errno,
            $errstr,
            $this->connectTimeout,
            $this->getStreamFlags(),
            $this->getStreamContext()
        );
        $this->checkSocket($this->socket, $errno, $errstr);
    }

    /**
     * @param resource|false $socket
     * @param int|null       $errno
     * @param string|null    $errstr
     *
     * @throws ConnectionException
     */
    protected function checkSocket($socket, int $errno = null, string $errstr = null): void
    {
        if ($socket === false || !is_resource($socket)) {
            $msg = sprintf(
                "Could not connect to the php-java-bridge server '%s'. Please start it. (err: %s, %s)",
                $this->address,
                $errno ?? 0,
                $errstr ?? 'Empty errstr returned'
            );
            throw new ConnectionException($msg);
        }
    }

    protected function getStreamFlags(): int
    {
        return $this->persistent ? STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT : STREAM_CLIENT_CONNECT;
    }

    /**
     * @return resource
     */
    protected function getStreamContext()
    {
        return stream_context_create($this->streamContext);
    }
}

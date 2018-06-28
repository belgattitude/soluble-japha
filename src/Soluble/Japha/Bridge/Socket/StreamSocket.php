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

    public const DEFAULT_CONNECT_TIMEOUTS = [
        'HOST_127.0.0.1' => 5.0,
        'HOST_localhost' => 5.0,
        'DEFAULT' => 20.0
    ];

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
     * @param float   $connectTimeout connection timeout in seconds (double: i.e: 5.0)
     * @param mixed[] $streamContext  see stream_context_create()
     * @param bool    $persistent     whether to use persistent connections
     */
    public function __construct(
        string $transport,
        string $address,
        float $connectTimeout = null,
        array $streamContext = self::DEFAULT_CONTEXT,
        bool $persistent = false
    ) {
        $this->setTransport($transport);
        $this->address = $address;
        $this->setConnectTimeout($connectTimeout);
        $this->streamContext = $streamContext;
        $this->persistent = $persistent;
        $this->createSocket();
    }

    protected function setConnectTimeout(float $timeout = null): void
    {
        if ($timeout === null) {
            list($host) = explode(':', $this->address);
            if (array_key_exists("HOST_$host", self::DEFAULT_CONNECT_TIMEOUTS)) {
                $timeout = self::DEFAULT_CONNECT_TIMEOUTS["HOST_$host"];
            } else {
                $timeout = self::DEFAULT_CONNECT_TIMEOUTS['DEFAULT'];
            }
        }
        $this->connectTimeout = $timeout;
    }

    /**
     * @throws InvalidArgumentException when getting an unsupported transport
     */
    protected function setTransport(string $transport): void
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

    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
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
     * Whether the connection is persistent or not.
     *
     * @return bool
     */
    public function isPersistent(): bool
    {
        return $this->persistent;
    }

    /**
     * @throws ConnectionException
     */
    protected function createSocket(): void
    {
        $socket = @stream_socket_client(
            $this->getStreamAddress(),
            $errno,
            $errstr,
            $this->connectTimeout,
            $this->getStreamFlags(),
            $this->getStreamContext()
        );

        if ($socket === false || !is_resource($socket)) {
            $msg = sprintf(
                "Could not connect to the php-java-bridge server '%s'. Please start it. (err: %s, %s)",
                $this->address,
                $errno ?? 0,
                $errstr ?? 'Empty errstr returned'
            );
            throw new ConnectionException($msg);
        }

        $this->socket = $socket;
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

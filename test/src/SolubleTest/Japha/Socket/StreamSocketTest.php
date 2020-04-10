<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Socket;

use Soluble\Japha\Bridge\Socket\StreamSocket;
use PHPUnit\Framework\TestCase;

class StreamSocketTest extends TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        \SolubleTestFactories::startJavaBridgeServer();

        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
    }

    public function testInvalidTransportThrowsInvalidArgumentException(): void
    {
        $this->expectException(\Soluble\Japha\Bridge\Exception\InvalidArgumentException::class);
        new StreamSocket('invalid', 'localhost');
    }

    public function testThrowsConnectionException(): void
    {
        $this->expectException(\Soluble\Japha\Bridge\Exception\ConnectionException::class);
        //$this->expectExceptionMessage('cooo');
        new StreamSocket(
            StreamSocket::TRANSPORT_TCP,
            '128.23.60.12:73567',
            0.5
        );
    }

    protected function getWorkingStreamSocket(): StreamSocket
    {
        [
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port
        ] = parse_url($this->servlet_address);

        $streamSocket = new StreamSocket(
            $scheme === 'https' ? StreamSocket::TRANSPORT_SSL : StreamSocket::TRANSPORT_TCP,
            "$host:$port",
            2.0
        );

        return $streamSocket;
    }

    public function testGetTransport(): void
    {
        $streamSocket = $this->getWorkingStreamSocket();
        [
            'scheme' => $scheme,
        ] = parse_url($this->servlet_address);
        $transport = $scheme === 'https' ? StreamSocket::TRANSPORT_SSL : StreamSocket::TRANSPORT_TCP;
        self::assertSame($transport, $streamSocket->getTransport());
    }

    public function testGetSocket(): void
    {
        $streamSocket = $this->getWorkingStreamSocket();
        self::assertInternalType('resource', $streamSocket->getSocket());
    }

    public function testGetStreamAddress(): void
    {
        $streamSocket = $this->getWorkingStreamSocket();
        [
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port
        ] = parse_url($this->servlet_address);
        $transport = $scheme === 'https' ? StreamSocket::TRANSPORT_SSL : StreamSocket::TRANSPORT_TCP;

        self::assertSame("$transport://$host:$port", $streamSocket->getStreamAddress());
    }

    public function testGetConnectTimeoutWithDefaults(): void
    {
        $streamSocketMock = $this->getMockBuilder(StreamSocket::class)
             ->enableOriginalConstructor()
             ->setMethods(['checkSocket', 'createSocket']);

        $streamSocket = $streamSocketMock->setConstructorArgs([
            StreamSocket::TRANSPORT_TCP,
            '127.0.0.1:8080',
            1.5
        ])->getMock();
        self::assertSame(1.5, $streamSocket->getConnectTimeout());

        $streamSocket = $streamSocketMock->setConstructorArgs([
            StreamSocket::TRANSPORT_TCP,
            '127.0.0.1:8080'
        ])->getMock();
        self::assertSame(StreamSocket::DEFAULT_CONNECT_TIMEOUTS['HOST_127.0.0.1'], $streamSocket->getConnectTimeout());

        $streamSocket = $streamSocketMock->setConstructorArgs([
            StreamSocket::TRANSPORT_TCP,
            'localhost:8080'
        ])->getMock();
        self::assertSame(StreamSocket::DEFAULT_CONNECT_TIMEOUTS['HOST_localhost'], $streamSocket->getConnectTimeout());

        $streamSocket = $streamSocketMock->setConstructorArgs([
            StreamSocket::TRANSPORT_TCP,
            '257.257.257.257:8080'
        ])->getMock();
        self::assertSame(StreamSocket::DEFAULT_CONNECT_TIMEOUTS['DEFAULT'], $streamSocket->getConnectTimeout());
    }

    public function testIsPersistent(): void
    {
        $streamSocketMock = $this->getMockBuilder(StreamSocket::class)
            ->enableOriginalConstructor()
            ->setMethods(['checkSocket', 'createSocket']);

        // DEFAULT
        $streamSocket = $streamSocketMock->setConstructorArgs([
            StreamSocket::TRANSPORT_TCP,
            '127.0.0.1:8080',
            null
        ])->getMock();
        self::assertFalse($streamSocket->isPersistent());

        // TRUE
        $streamSocket = $streamSocketMock->setConstructorArgs([
            StreamSocket::TRANSPORT_TCP,
            '127.0.0.1:8080',
            null,
            [],
            $persistent = true,
        ])->getMock();
        self::assertTrue($streamSocket->isPersistent());
    }
}

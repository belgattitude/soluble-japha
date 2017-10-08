<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Psr\Log\NullLogger;
use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\Client;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->adapter = new Adapter([
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ]);
        $this->client = $this->adapter->getDriver()->getClient()->getClient();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testCommon()
    {
        $conn = PjbProxyClient::parseServletUrl($this->servlet_address);
        $params = new \ArrayObject([
            Client::PARAM_JAVA_HOSTS => $conn['servlet_host'],
            Client::PARAM_JAVA_SERVLET => $conn['servlet_uri'],
            Client::PARAM_JAVA_SEND_SIZE => 4096,
            Client::PARAM_JAVA_RECV_SIZE => 8192,
            Client::PARAM_JAVA_INTERNAL_ENCODING => 'ISO-8859-1'
        ]);

        $client = new Client($params, new NullLogger());

        self::assertSame(4096, $client->java_send_size);
        self::assertSame(8192, $client->java_recv_size);
        self::assertSame('ISO-8859-1', $client->getParam(Client::PARAM_JAVA_INTERNAL_ENCODING));
        self::assertInstanceOf(NullLogger::class, $client->getLogger());
        self::assertEquals($conn['servlet_host'], $client->getServerName());
        $enc = $this->client->getParam(Client::PARAM_JAVA_INTERNAL_ENCODING);
        self::assertEquals('UTF-8', $enc);
    }

    public function testDefaults()
    {
        $conn = PjbProxyClient::parseServletUrl($this->servlet_address);
        $params = new \ArrayObject([
            'JAVA_HOSTS' => $conn['servlet_host'],
            'JAVA_SERVLET' => $conn['servlet_uri'],
        ]);

        $client = new Client($params, new NullLogger());
        self::assertEquals(Client::DEFAULT_PARAMS[Client::PARAM_JAVA_SEND_SIZE], $client->java_send_size);
        self::assertEquals(Client::DEFAULT_PARAMS[Client::PARAM_JAVA_RECV_SIZE], $client->java_recv_size);
    }

    public function testSetHandler()
    {
        $conn = PjbProxyClient::parseServletUrl($this->servlet_address);
        $params = new \ArrayObject([
            'JAVA_HOSTS' => $conn['servlet_host'],
            'JAVA_SERVLET' => $conn['servlet_uri'],
        ]);

        $client = new Client($params, new NullLogger());
        $client->setDefaultHandler();

        $this->client->setAsyncHandler();
        self::assertEquals($client->methodCache, $client->asyncCache);
    }

    public function testSetExitCode()
    {
        $conn = PjbProxyClient::parseServletUrl($this->servlet_address);
        $params = new \ArrayObject([
            'JAVA_HOSTS' => $conn['servlet_host'],
            'JAVA_SERVLET' => $conn['servlet_uri'],
            'JAVA_SEND_SIZE' => 4096,
            'JAVA_RECV_SIZE' => 8192
        ]);

        $client = new Client($params, new NullLogger());
        $client->setExitCode(1);
    }
}

<?php

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Psr\Log\NullLogger;
use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\Client;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;

class ClientTest extends \PHPUnit_Framework_TestCase
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
            'JAVA_HOSTS' => $conn['servlet_host'],
            'JAVA_SERVLET' => $conn['servlet_uri'],
            'JAVA_SEND_SIZE' => 4096,
            'JAVA_RECV_SIZE' => 8192,
            'internal_encoding' => 'ISO-8859-1'
        ]);

        $client = new Client($params, new NullLogger());

        $this->assertEquals(4096, $client->java_send_size);
        $this->assertEquals(8192, $client->java_recv_size);
        $this->assertEquals('ISO-8859-1', $client->getInternalEncoding());
        $this->assertInstanceOf(NullLogger::class, $client->getLogger());
        $this->assertEquals($params, $client->getParams());
        $this->assertEquals($conn['servlet_host'], $client->getServerName());
        $enc = $this->client->getInternalEncoding();
        $this->assertEquals('UTF-8', $enc);
    }

    public function testDefaults()
    {
        $conn = PjbProxyClient::parseServletUrl($this->servlet_address);
        $params = new \ArrayObject([
            'JAVA_HOSTS' => $conn['servlet_host'],
            'JAVA_SERVLET' => $conn['servlet_uri'],
        ]);

        $client = new Client($params, new NullLogger());
        $this->assertEquals(8192, $client->java_send_size);
        $this->assertEquals(8192, $client->java_recv_size);
        $this->assertEquals('UTF-8', $client->getInternalEncoding());
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
        $client->setExitCode(0);
    }

    public function testGetInternalEncoding()
    {
        $enc = $this->client->getInternalEncoding();
        $this->assertEquals('UTF-8', $enc);
    }
}

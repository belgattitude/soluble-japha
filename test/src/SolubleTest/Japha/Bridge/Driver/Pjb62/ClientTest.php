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
use Soluble\Japha\Bridge\Driver\Pjb62\NativeParser;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;
use PHPUnit\Framework\TestCase;
use Soluble\Japha\Bridge\Driver\Pjb62\Protocol;

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
        $this->assertEquals($client->methodCache, $client->asyncCache);
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
        /*
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();


        $protocolStub = $this->prophesize(Protocol::class);
        $protocolStub->writeExitCode()->shouldBeCalled();
        $protocolStub->writeExitCode();

        $clientMock->protocol = $protocolStub;
        $clientMock->setExitCode(0);
        */
    }

    public function testGetInternalEncoding()
    {
        $enc = $this->client->getInternalEncoding();
        $this->assertEquals('UTF-8', $enc);
    }

    /*
        public function testUnsupportedXMLWillCallClientParserError()
        {
            // SHOULD BE TESTED
            //$this->client->begin('G', []);
    
            $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->disableOriginalClone()
                ->disableArgumentCloning()
                ->disallowMockingUnknownTypes()
                ->getMock();
    
            $clientMock->method('read')
                ->withAnyParameters()
                // Unsupported protocol object identification
                ->willReturn('<G v="efd" m="sun.util.calendar.ZoneInfo" p="O" n="T"/>');
    
            $parserStub = $this->prophesize(NativeParser::class);
            $parserStub->parserError()->shouldBeCalled();
    
            $clientMock->parser = $parserStub->reveal();
    
    
        }
    */
}

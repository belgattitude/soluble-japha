<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Prophecy\Argument;
use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\Client;
use Soluble\Japha\Bridge\Driver\Pjb62\NativeParser;
use Soluble\Japha\Bridge\Driver\Pjb62\SocketHandler;

class NativeParserTest extends \PHPUnit_Framework_TestCase
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

    public function testParseError()
    {
        //$clientStub = $this->prophesize(Client::class);
        //$clientStub->read()->with($this->client->java_recv_size)->willReturn($this->returnValue('<xml></xml>'));
        //$clientMock = $this->createMock(Client::class);

        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $clientMock->method('read')
                    ->withAnyParameters()
                    // Invalid xml
                    ->willReturn('<xml><a><<c>/a></xml>');

        $handlerStub = $this->prophesize(SocketHandler::class);
        $handlerStub->shutdownBrokenConnection(Argument::containingString('protocol error'))
                    ->shouldBeCalled();

        $clientMock->protocol = new \stdClass();
        $clientMock->protocol->handler = $handlerStub->reveal();

        $parser = new NativeParser($clientMock);
        $parser->parse();
    }

    public function testParserGetDataBase64()
    {
        $nativeParser = new NativeParser($this->client);
        $this->assertEquals('你好，世界', $nativeParser->getData(base64_encode('你好，世界')));
    }

    protected function getNativeParserMock()
    {
        $stub = $this->createMock(NativeParser::class);
        $stub->method('getData')
            ->will($this->returnCallback('base64_decode'));
    }
}

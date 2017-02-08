<?php

namespace SolubleTest\Japha\Bridge;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\ClientInterface;
use Soluble\Japha\Bridge\Driver\DriverInterface;
use Soluble\Japha\Interfaces\JavaObject;

class DriverContextTest extends \PHPUnit_Framework_TestCase
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
     * @var DriverInterface
     */
    protected $driver;

    protected function setUp()
    {
        \SolubleTestFactories::startJavaBridgeServer();
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->adapter = new Adapter([
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ]);
        $this->driver = $this->adapter->getDriver();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetClient()
    {
        $client = $this->driver->getClient();
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testContext()
    {
        $context = $this->driver->getContext();
        $this->assertInstanceOf(JavaObject::class, $context);

        $className = $this->driver->getClassName($context);

        $supported = [
            // Denote standalone version
            'php.java.bridge.http.Context',

            // Before 6.2.11 phpjavabridge version
            'php.java.servlet.HttpContext',
            // From 6.2.11 phpjavabridge version
            'io.soluble.pjb.servlet.HttpContext'
        ];

        $this->assertContains($className, $supported);
    }
}

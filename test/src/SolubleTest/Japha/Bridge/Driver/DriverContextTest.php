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
            // Before 6.2.11 phpjavabridge version
            'php.java.servlet.HttpContext' => 'servlet',
            // From 6.2.11 phpjavabridge version
            'io.soluble.pjb.servlet.HttpContext' => 'servlet',

            // For standalone
            'io.soluble.pjb.bridge.http.Context' => 'standalone',
            'php.java.bridge.http.Context' => 'standalone',
        ];

        $this->assertContains($className, array_keys($supported));

        if ($supported[$className] == 'servlet') {
            // Those tests does not make sense with the standalone
            $httpServletRequest = $context->getHttpServletRequest();
            $this->assertInstanceOf(JavaObject::class, $httpServletRequest);
            $this->assertContains($this->driver->getClassName($httpServletRequest), [
                'io.soluble.pjb.servlet.RemoteHttpServletRequest',
                'php.java.servlet.RemoteServletRequest'
            ]);

            echo $this->driver->inspect($httpServletRequest);

            $this->assertEquals('java.util.Locale',
                $this->driver->getClassName($httpServletRequest->getLocale()));

            $this->assertEquals('java.lang.String',
                $this->driver->getClassName($httpServletRequest->getMethod()));

            $this->assertEquals('java.lang.String',
                $this->driver->getClassName($httpServletRequest->getProtocol()));

            $this->assertContains('HTTP', (string) $httpServletRequest->getProtocol());

            $headerNames = $httpServletRequest->getHeaderNames();

            $this->assertContains('Enum', $this->driver->getClassName($headerNames));

            $headers = [];
            while ($headerNames->hasMoreElements()) {
                $name = $headerNames->nextElement();
                $value = $httpServletRequest->getHeader($name);
                $headers[(string) $name] = (string) $value;
            }

            $this->assertArrayHasKey('host', $headers); // 127.0.0.1:8080 (tomcat listening address)
            $this->assertArrayHasKey('cache-control', $headers);
            $this->assertArrayHasKey('transfert-encoding', $headers);
        }
    }
}

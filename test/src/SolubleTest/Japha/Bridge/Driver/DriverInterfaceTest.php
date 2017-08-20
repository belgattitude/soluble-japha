<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\DriverInterface;
use PHPUnit\Framework\TestCase;

class DriverInterfaceTest extends TestCase
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

    public function testGetClassName()
    {
        $javaString = $this->adapter->java('java.lang.String', 'Hello World');
        $className = $this->driver->getClassName($javaString);
        $this->assertEquals('java.lang.String', $className);
    }

    public function testInspect()
    {
        $javaString = $this->adapter->java('java.lang.String', 'Hello World');
        $inspected = $this->driver->inspect($javaString);
        $this->assertInternalType('string', $inspected);
        $this->assertStringStartsWith('[class java.lang.String:', $inspected);
        $this->assertContains('Constructors:', $inspected);
        $this->assertContains('Fields:', $inspected);
        $this->assertContains('Methods:', $inspected);
        $this->assertContains('Classes:', $inspected);
    }

    public function testInvoke()
    {
        $javaString = $this->adapter->java('java.lang.String', 'Hello');
        $length = $this->driver->invoke($javaString, 'length');
        $this->assertEquals(5, $length);
        $this->assertEquals($javaString->length(), $length);

        // Multiple arguments
        $javaString = $this->adapter->java('java.lang.String', 'Hello World! World!');

        $indexStart = $this->driver->invoke($javaString, 'indexOf', ['World']);
        $index12 = $this->driver->invoke($javaString, 'indexOf', ['World', $fromIndex = 12]);
        $index16 = $this->driver->invoke($javaString, 'indexOf', ['World', $fromIndex = 16]);

        $this->assertEquals(6, $indexStart);
        $this->assertEquals(13, $index12);
        $this->assertEquals(-1, $index16);
    }

    public function testInvokeWithClass()
    {
        $javaClass = $this->adapter->javaClass('java.lang.System');
        $invokedVersion = $this->driver->invoke($javaClass, 'getProperty', ['java.version']);
        $javaVersion = $javaClass->getProperty('java.version');
        $this->assertEquals((string) $javaVersion, (string) $invokedVersion);
    }
}

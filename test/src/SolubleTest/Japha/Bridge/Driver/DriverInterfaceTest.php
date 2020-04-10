<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
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
        self::assertEquals('java.lang.String', $className);
    }

    public function testInspect()
    {
        $javaString = $this->adapter->java('java.lang.String', 'Hello World');
        $inspected = $this->driver->inspect($javaString);
        self::assertInternalType('string', $inspected);
        self::assertStringStartsWith('[class java.lang.String:', $inspected);
        self::assertContains('Constructors:', $inspected);
        self::assertContains('Fields:', $inspected);
        self::assertContains('Methods:', $inspected);
        self::assertContains('Classes:', $inspected);
    }

    public function testInvoke()
    {
        $javaString = $this->adapter->java('java.lang.String', 'Hello');
        $length = $this->driver->invoke($javaString, 'length');
        self::assertEquals(5, $length);
        self::assertEquals($javaString->length(), $length);

        // Multiple arguments
        $javaString = $this->adapter->java('java.lang.String', 'Hello World! World!');

        $indexStart = $this->driver->invoke($javaString, 'indexOf', ['World']);
        $index12 = $this->driver->invoke($javaString, 'indexOf', ['World', $fromIndex = 12]);
        $index16 = $this->driver->invoke($javaString, 'indexOf', ['World', $fromIndex = 16]);

        self::assertEquals(6, $indexStart);
        self::assertEquals(13, $index12);
        self::assertEquals(-1, $index16);
    }

    public function testInvokeWithClass()
    {
        $javaClass = $this->adapter->javaClass('java.lang.System');
        $invokedVersion = $this->driver->invoke($javaClass, 'getProperty', ['java.version']);
        $javaVersion = $javaClass->getProperty('java.version');
        self::assertEquals((string) $javaVersion, (string) $invokedVersion);
    }
}

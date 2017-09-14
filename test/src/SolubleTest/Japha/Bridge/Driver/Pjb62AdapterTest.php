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
use Soluble\Japha\Bridge\Exception\NoSuchFieldException;
use PHPUnit\Framework\TestCase;

class Pjb62AdapterTest extends TestCase
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
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetDriver()
    {
        $driver = $this->adapter->getDriver();
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Pjb62Driver', $driver);
    }

    public function testJavaThrowsClassNotFoundException()
    {
        $this->expectException('Soluble\Japha\Bridge\Exception\ClassNotFoundException');
        $string = $this->adapter->java('java.util.String', 'Am I the only one ?');
    }

    public function testJavaThrowsNoSuchMethodException()
    {
        $this->expectException('Soluble\Japha\Bridge\Exception\NoSuchMethodException');
        $string = $this->adapter->java('java.lang.String', 'Am I the only one ?');
        $string->myinvalidMethod();
    }

    public function testJavaThrowsNoSuchFieldException()
    {
        $this->expectException(NoSuchFieldException::class);
        $this->adapter->java('java.lang.String')->nosuchfield = 10;
    }

    public function testJavaStrings()
    {
        $ba = $this->adapter;

        // ascii
        $string = $ba->java('java.lang.String', 'Am I the only one ?');
        self::assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Java', $string);
        self::assertEquals('Am I the only one ?', $string);
        self::assertNotEquals('Am I the only one', $string);

        // unicode - utf8
        $string = $ba->java('java.lang.String', '保障球迷權益');
        self::assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Java', $string);
        self::assertEquals('保障球迷權益', $string);
        self::assertNotEquals('保障球迷', $string);
    }

    public function testJavaHashMap()
    {
        $ba = $this->adapter;
        $hash = $ba->java('java.util.HashMap', ['my_key' => 'my_value']);
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Java', $hash);
        self::assertEquals('my_value', $hash->get('my_key'));
        $hash->put('new_key', 'oooo');
        self::assertEquals('oooo', $hash->get('new_key'));
        $hash->put('new_key', 'pppp');
        self::assertEquals('pppp', $hash->get('new_key'));

        self::assertEquals(4, $hash->get('new_key')->length());

        $hash->put('key', $ba->java('java.lang.String', '保障球迷權益'));
        self::assertEquals('保障球迷權益', $hash->get('key'));
        self::assertEquals(6, $hash->get('key')->length());
    }

    public function testJavaClass()
    {
        $ba = $this->adapter;
        $cls = $ba->javaClass('java.lang.Class');
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\JavaClass', $cls);
        self::assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $cls);
    }

    public function testJavaSystemClass()
    {
        $ba = $this->adapter;

        $system = $ba->javaClass('java.lang.System');
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\JavaClass', $system);
        self::assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $system);

        $properties = $system->getProperties();
        self::assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $properties);
        //self::assertInternalType('string', $properties->__cast('string'));
        //self::assertInternalType('string', $properties->__toString());

        $vm_name = $properties->get('java.vm.name');
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\InternalJava', $vm_name);
    }

    public function testIterator()
    {
        $ba = $this->adapter;

        $system = $ba->javaClass('java.lang.System');
        $properties = $system->getProperties();

        foreach ($properties as $key => $value) {
            self::assertInternalType('string', $key);
            self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\InternalJava', $value);
        }

        $iterator = $properties->getIterator();
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\ObjectIterator', $iterator);
        self::assertInstanceOf('Iterator', $iterator);
    }
}

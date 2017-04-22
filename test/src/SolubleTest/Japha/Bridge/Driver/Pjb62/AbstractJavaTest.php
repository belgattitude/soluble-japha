<?php

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\AbstractJava;
use Soluble\Japha\Bridge\Driver\Pjb62\InternalJava;
use Soluble\Japha\Bridge\Exception\NoSuchMethodException;
use Soluble\Japha\Interfaces\JavaObject;

class AbstractJavaTest extends \PHPUnit_Framework_TestCase
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

    public function testArrayAccessOffsetExists()
    {
        $ba = $this->adapter;
        $arrayList = $ba->java('java.util.ArrayList');
        $this->assertInstanceOf(AbstractJava::class, $arrayList);

        $this->assertFalse(isset($arrayList[0]));
        $arrayList->add('Hello');
        $this->assertTrue(isset($arrayList[0]));

        $hashMap = $ba->java('java.util.HashMap');
        $this->assertInstanceOf(AbstractJava::class, $hashMap);

        $this->assertFalse(isset($hashMap['key']));
        $hashMap->put('key', 'value');
        $this->assertTrue(isset($hashMap['key']));
    }

    public function testCustomOffsetExists()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap->put('key', 'value');

        try {
            // We bypass the regular php method offsetExists
            // because we add more parameters.
            // so the method will be called on HashMap
            // HashMap.offsetExists('key', 'param1', 'param2'
            // and does not exists
            $hashMap->offsetExists('key', 'param1', 'param2');
            $this->assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            $this->assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testArrayAccessOffsetGet()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap->put('key', 'value');

        $this->assertEquals('value', $hashMap['key']);

        try {
            $hashMap->offsetGet('key', 'param1', 'param2');
            $this->assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            $this->assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testArrayAccessOffsetSet()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap['key'] = 'value';
        $this->assertEquals('value', $hashMap['key']);

        try {
            $hashMap->offsetSet('key', 'param1', 'param2');
            $this->assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            $this->assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testArrayAccessOffsetUnset()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap['key'] = 'value';
        $this->assertEquals('value', $hashMap['key']);

        unset($hashMap['key']);
        $this->assertFalse(isset($hashMap['key']));

        try {
            $hashMap->offsetUnset('key', 'param1', 'param2');
            $this->assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            $this->assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testGetIterator()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap['key'] = 'value';
        foreach ($hashMap as $key => $value) {
            $this->assertEquals('key', $key);
            $this->assertEquals('value', $value);
        }

        try {
            $hashMap->getIterator('key', 'param1', 'param2');
            $this->assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            $this->assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testGetClass()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');
        $c = $hashMap->getClass();
        $this->assertInstanceOf(InternalJava::class, $c);
        $this->assertInstanceOf(JavaObject::class, $c);
    }
}

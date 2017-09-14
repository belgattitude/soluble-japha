<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\AbstractJava;
use Soluble\Japha\Bridge\Driver\Pjb62\InternalJava;
use Soluble\Japha\Bridge\Exception\NoSuchFieldException;
use Soluble\Japha\Bridge\Exception\NoSuchMethodException;
use Soluble\Japha\Interfaces\JavaObject;
use PHPUnit\Framework\TestCase;

class AbstractJavaTest extends TestCase
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
        self::assertInstanceOf(AbstractJava::class, $arrayList);

        self::assertFalse(isset($arrayList[0]));
        $arrayList->add('Hello');
        self::assertTrue(isset($arrayList[0]));

        $hashMap = $ba->java('java.util.HashMap');
        self::assertInstanceOf(AbstractJava::class, $hashMap);

        self::assertFalse(isset($hashMap['key']));
        $hashMap->put('key', 'value');
        self::assertTrue(isset($hashMap['key']));
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
            self::assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            self::assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testArrayAccessOffsetGet()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap->put('key', 'value');

        self::assertEquals('value', $hashMap['key']);

        try {
            $hashMap->offsetGet('key', 'param1', 'param2');
            self::assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            self::assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testArrayAccessOffsetSet()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap['key'] = 'value';
        self::assertEquals('value', $hashMap['key']);

        try {
            $hashMap->offsetSet('key', 'param1', 'param2');
            self::assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            self::assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testArrayAccessOffsetUnset()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap['key'] = 'value';
        self::assertEquals('value', $hashMap['key']);

        unset($hashMap['key']);
        self::assertFalse(isset($hashMap['key']));

        try {
            $hashMap->offsetUnset('key', 'param1', 'param2');
            self::assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            self::assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testGetIterator()
    {
        $ba = $this->adapter;

        $hashMap = $ba->java('java.util.HashMap');

        $hashMap['key'] = 'value';
        foreach ($hashMap as $key => $value) {
            self::assertEquals('key', $key);
            self::assertEquals('value', $value);
        }

        try {
            $hashMap->getIterator('key', 'param1', 'param2');
            self::assertTrue(false, 'Method should not exists on HashMap');
        } catch (NoSuchMethodException $e) {
            self::assertTrue(true, 'Method does not exists as expected');
        }
    }

    public function testMagicSet()
    {
        $ba = $this->adapter;
        $hashMap = $ba->java('java.util.HashMap');
        try {
            $hashMap->aProperty = 'cool';
            self::assertTrue(false, 'Property should not exists on HashMap');
        } catch (NoSuchFieldException $e) {
            self::assertTrue(true, 'Property does not exists as expected');
        }
    }

    public function testGetClass()
    {
        $ba = $this->adapter;
        $hashMap = $ba->java('java.util.HashMap');
        $c = $hashMap->getClass();
        self::assertInstanceOf(InternalJava::class, $c);
        self::assertInstanceOf(JavaObject::class, $c);
    }
}

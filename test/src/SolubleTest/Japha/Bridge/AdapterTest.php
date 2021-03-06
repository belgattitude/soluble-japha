<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge;

use Soluble\Japha\Bridge\Adapter;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-04 at 16:47:42.
 */
class AdapterTest extends TestCase
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
    protected function setUp(): void
    {
        \SolubleTestFactories::startJavaBridgeServer();

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
    protected function tearDown(): void
    {
    }

    public function testGetDriver()
    {
        $driver = $this->adapter->getDriver();
        self::assertInstanceOf('Soluble\Japha\Bridge\Driver\AbstractDriver', $driver);
    }

    public function testJavaClass()
    {
        $ba = $this->adapter;
        $cls = $ba->javaClass('java.lang.Class');
        self::assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $cls);
    }

    public function testValues()
    {
        $ba = $this->adapter;

        $array = array_fill(0, 1000, 'Hello');
        $vector = $ba->java('java.util.Vector', $array);

        $values = $ba->values($vector);
        self::assertEquals($array, $ba->values($vector));

        $arrOfArray = [
            'real' => true,
            'what' => 'Too early to know',
            'count' => 2017,
            'arr10000' => array_fill(0, 10000, 'Hello world')
        ];

        $hashMap = $ba->java('java.util.HashMap', $arrOfArray);
        $arrFromJava = $ba->values($hashMap);

        self::assertEquals($arrOfArray, $arrFromJava);
    }

    public function testIsInstanceOf()
    {
        $ba = $this->adapter;

        $system = $ba->javaClass('java.lang.System');
        $string = $ba->java('java.lang.String', 'Hello');
        $bigint = $ba->java('java.math.BigInteger', 1234567890123);
        $hash = $ba->java('java.util.HashMap', []);

        self::assertFalse($ba->isInstanceOf($system, $string));
        self::assertFalse($ba->isInstanceOf($hash, $string));
        self::assertTrue($ba->isInstanceOf($string, 'java.lang.String'));
        self::assertFalse($ba->isInstanceOf($string, 'java.util.HashMap'));
        self::assertTrue($ba->isInstanceOf($hash, 'java.util.HashMap'));
        self::assertTrue($ba->isInstanceOf($bigint, 'java.math.BigInteger'));
        self::assertTrue($ba->isInstanceOf($bigint, 'java.lang.Object'));
        self::assertTrue($ba->isInstanceOf($hash, 'java.lang.Object'));

        self::assertFalse($ba->isInstanceOf($system, 'java.lang.System'));
    }

    public function testIsNull()
    {
        $ba = $this->adapter;
        self::assertTrue($ba->isNull(null));
        self::assertTrue($ba->isNull());

        $system = $ba->javaClass('java.lang.System');
        self::assertFalse($ba->isNull($system));

        $emptyString = $ba->java('java.lang.String', '');
        self::assertFalse($ba->isNull($emptyString));

        //because in this case it's empty
        $nullString = $ba->java('java.lang.String');
        self::assertFalse($ba->isNull($nullString));

        $v = $ba->java('java.util.Vector', [1, 2, 3]);
        $v->add(1, null);
        $v->add(2, 0);

        self::assertTrue($ba->isNull($v->get(1)));
        self::assertFalse($ba->isNull($v->get(2)));
    }

    public function testIsTrue()
    {
        $ba = $this->adapter;

        $b = $ba->java('java.lang.Boolean', true);
        self::assertTrue($ba->isTrue($b));

        $b = $ba->java('java.lang.Boolean', false);
        self::assertFalse($ba->isTrue($b));

        // initial capacity of 10
        $v = $ba->java('java.util.Vector', [1, 2, 3, 4, 5]);
        self::assertFalse($ba->isTrue($v));

        $v->add(1, 1);
        $v->add(2, $ba->java('java.lang.Boolean', true));
        $v->add(3, $ba->java('java.lang.Boolean', false));
        $v->add(4, true);
        $v->add(5, false);

        self::assertTrue($ba->isTrue($v->get(1)));
        self::assertTrue($ba->isTrue($v->get(2)));
        self::assertTrue(!$ba->isTrue($v->get(3)));
        self::assertTrue($ba->isTrue($v->get(4)));
        self::assertTrue(!$ba->isTrue($v->get(5)));

        // Empty string are considered as false
        $s = $ba->java('java.lang.String');
        self::assertFalse($ba->isTrue($s));

        $s = $ba->java('java.lang.String', '');
        self::assertFalse($ba->isTrue($s));

        $s = $ba->java('java.lang.String', 'true');
        self::assertFalse($ba->isTrue($s));

        $s = $ba->java('java.lang.String', '1');
        self::assertFalse($ba->isTrue($s));

        self::assertTrue($ba->isTrue($ba->java('java.lang.Boolean', 1)));
        self::assertTrue($ba->isTrue($ba->java('java.lang.Boolean', true)));

        self::assertFalse($ba->isTrue($ba->java('java.lang.Boolean', 0)));
        self::assertFalse($ba->isTrue($ba->java('java.lang.Boolean', false)));
    }

    public function testGetClassName()
    {
        $javaString = $this->adapter->java('java.lang.String', 'Hello World');
        $className = $this->adapter->getClassName($javaString);
        self::assertEquals('java.lang.String', $className);
    }

    public function testGetSystem()
    {
        $system = $this->adapter->getSystem();
        self::assertInstanceOf('Soluble\Japha\Bridge\Adapter\System', $system);
    }
}

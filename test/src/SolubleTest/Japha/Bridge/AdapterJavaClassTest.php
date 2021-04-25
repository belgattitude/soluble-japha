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
use Soluble\Japha\Bridge\Exception\ClassNotFoundException;
use Soluble\Japha\Interfaces\JavaClass;
use Soluble\Japha\Interfaces\JavaObject;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-04 at 16:47:42.
 */
class AdapterJavaClassTest extends TestCase
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

    public function testGetName()
    {
        $ba = $this->adapter;

        $cls = $ba->javaClass('java.util.HashMap');
        $className = $cls->getName();
        self::assertIsString($className);
        self::assertEquals('java.util.HashMap', $className);
    }

    public function testGetClassOnClass()
    {
        $ba = $this->adapter;

        $cls = $ba->javaClass('java.util.HashMap');
        $class = $cls->getClass();
        self::assertInstanceOf(JavaObject::class, $class);
        // @TODO possible bc-break, makes this type of call returning a
        // JavaClass
        //self::assertInstanceOf(JavaClass::class, $class);
        self::assertEquals('java.lang.Class', $class->getName());
    }

    public function testJavaClassThrowsClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);

        $ba = $this->adapter;

        $cls = $ba->javaClass('java.INVALIDPKG.HashMap');
    }
}

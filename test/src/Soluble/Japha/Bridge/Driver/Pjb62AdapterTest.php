<?php

namespace Soluble\Japha\Bridge\Driver;

use Soluble\Japha\Bridge\Adapter;

class Pjb62AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var string
     */
    protected $servlet_address;


    /**
     *
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
        $this->adapter = new Adapter(array(
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ));

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
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Pjb62Driver', $driver);
    }



    public function testJavaThrowsClassNotFoundException()
    {
        $this->setExpectedException('Soluble\Japha\Bridge\Exception\ClassNotFoundException');
        $string = $this->adapter->java('java.util.String', "Am I the only one ?");
    }

    public function testJavaThrowsNoSuchMethodException()
    {
        $this->setExpectedException('Soluble\Japha\Bridge\Exception\NoSuchMethodException');
        $string = $this->adapter->java('java.lang.String', "Am I the only one ?");
        $string->myinvalidMethod();
    }

    public function testJavaStrings()
    {
        $ba = $this->adapter;

        // ascii
        $string = $ba->java('java.lang.String', "Am I the only one ?");
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Java', $string);
        $this->assertEquals('Am I the only one ?', $string);
        $this->assertNotEquals('Am I the only one', $string);

        // unicode - utf8
        $string = $ba->java('java.lang.String', "保障球迷權益");
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Java', $string);
        $this->assertEquals('保障球迷權益', $string);
        $this->assertNotEquals('保障球迷', $string);
    }


    public function testJavaHashMap()
    {
        $ba = $this->adapter;
        $hash = $ba->java('java.util.HashMap', array('my_key' => 'my_value'));
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Java', $hash);
        $this->assertEquals('my_value', $hash->get('my_key'));
        $hash->put('new_key', 'oooo');
        $this->assertEquals('oooo', $hash->get('new_key'));
        $hash->put('new_key', 'pppp');
        $this->assertEquals('pppp', $hash->get('new_key'));

        $this->assertEquals(4, $hash->get('new_key')->length());

        $hash->put('key', $ba->java('java.lang.String', "保障球迷權益"));
        $this->assertEquals('保障球迷權益', $hash->get('key'));
        $this->assertEquals(6, $hash->get('key')->length());
    }

    public function testJavaClass()
    {
        $ba = $this->adapter;
        $cls = $ba->javaClass('java.lang.Class');
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\JavaClass', $cls);
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $cls);
    }

    public function testJavaSystemClass()
    {
        $ba = $this->adapter;

        $system = $ba->javaClass('java.lang.System');
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\JavaClass', $system);
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $system);


        $properties = $system->getProperties();
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $properties);
        //$this->assertInternalType('string', $properties->__cast('string'));
        //$this->assertInternalType('string', $properties->__toString());

        $vm_name = $properties->get('java.vm.name');
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\InternalJava', $vm_name);

    }

    public function testIterator()
    {

        $ba = $this->adapter;

        $system = $ba->javaClass('java.lang.System');
        $properties = $system->getProperties();

        foreach ($properties as $key => $value) {
            $this->assertInternalType('string', $key);
            $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\InternalJava', $value);
        }

        $iterator = $properties->getIterator();
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\ObjectIterator', $iterator);
        $this->assertInstanceOf('Iterator', $iterator);
    }

    public function testDate()
    {
        $ba = $this->adapter;

        $pattern = "yyyy-MM-dd";
        $formatter = $ba->java("java.text.SimpleDateFormat", $pattern);

        $first = $formatter->format(new Pjb62\Java("java.util.Date", 0));
        $this->assertEquals('1970-01-01', $first);

        $now = $formatter->format($ba->java("java.util.Date"));
        $this->assertEquals(date('Y-m-d'), $now);
    }
}

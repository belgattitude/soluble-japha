<?php

namespace Soluble\Japha\Bridge\Driver;

use Soluble\Japha\Bridge\Adapter;

class Pjb621AdapterTest extends \PHPUnit_Framework_TestCase
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
            'driver' => 'Pjb621',
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
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb621\Pjb621Driver', $driver);
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
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb621\Java', $string);
        $this->assertEquals('Am I the only one ?', $string);
        $this->assertNotEquals('Am I the only one', $string);
        
        // unicode - utf8
        $string = $ba->java('java.lang.String', "保障球迷權益");
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb621\Java', $string);
        $this->assertEquals('保障球迷權益', $string);        
        $this->assertNotEquals('保障球迷', $string);        
    }            

    
    public function testJavaHashMap()
    {
        $ba = $this->adapter;
        $hash = $ba->java('java.util.HashMap', array('my_key' => 'my_value'));
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb621\Java', $hash);
        $this->assertEquals('my_value', $hash->get('my_key'));
        $hash->put('new_key', 'oooo');
        $this->assertEquals('oooo', $hash->get('new_key'));
        $hash->put('new_key', 'pppp');
        $this->assertEquals('pppp', $hash->get('new_key'));
    }            
    

}
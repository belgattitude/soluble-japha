<?php

namespace SolubleTest\Japha\Bridge\Driver;

use Soluble\Japha\Bridge\Adapter;

class DriverValuesTest extends \PHPUnit_Framework_TestCase
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

    public function testValues()
    {
        $ba = $this->adapter;

        $arrOfArray = [
            'real' => true,
            'what' => 'nothing',
            'arr10000' => array_fill(0, 10000, 'Hello world')
        ];

        $hashMap = $ba->java('java.util.HashMap', $arrOfArray);
        $arrFromJava = $ba->getDriver()->values($hashMap);

        $this->assertEquals($arrOfArray, $arrFromJava);
    }
}

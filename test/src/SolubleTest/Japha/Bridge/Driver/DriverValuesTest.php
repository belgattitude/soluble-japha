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
use PHPUnit\Framework\TestCase;

class DriverValuesTest extends TestCase
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
            'what' => 'Too early to know',
            'count' => 2017,
            'arr10000' => array_fill(0, 10000, 'Hello world')
        ];

        $hashMap = $ba->java('java.util.HashMap', $arrOfArray);
        $arrFromJava = $ba->getDriver()->values($hashMap);

        self::assertEquals($arrOfArray, $arrFromJava);
    }
}

<?php

namespace SolubleTest\Japha\Util;

use Soluble\Japha\Bridge;
use Soluble\Japha\Util\TimeZone;
use Soluble\Japha\Interfaces;
use DateTimeZone;

class TimeZoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * @var Bridge\Adapter
     */
    protected $ba;

    /**
     * @var TimeZone
     */
    protected $timeZone;

    /**
     * @var Interfaces\JavaObject
     */
    protected $backupTz;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        \SolubleTestFactories::startJavaBridgeServer();
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->ba = new Bridge\Adapter([
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ]);

        $this->timeZone = new TimeZone($this->ba);
        $this->backupTz = $this->ba->javaClass('java.util.TimeZone')->getDefault();
        //var_dump($this->backupTz);
        //die();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        if ($this->ba !== null) {
            $this->ba->javaClass('java.util.TimeZone')->setDefault($this->backupTz);
            TimeZone::enableTzCache();
        }
    }

    public function testGetAvailableIDs()
    {
        $availableTz = $this->timeZone->getAvailableIDs();
        $this->assertInternalType('array', $availableTz);
        $this->assertContains('Europe/Paris', $availableTz);
    }

    public function testGetDefault()
    {
        $default = $this->timeZone->getDefault();
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $default);
        $iof = $this->ba->isInstanceOf($default, 'java.util.TimeZone');
        $this->assertTrue($iof);
    }

    public function testGetTimezone()
    {
        $ids = ['Europe/Paris', 'CET', 'GMT'];
        foreach ($ids as $id) {
            $tz = $this->timeZone->getTimeZone($id);
            $iof = $this->ba->isInstanceOf($tz, 'java.util.TimeZone');
            $this->assertTrue($iof);
            $this->assertEquals($id, (string) $tz->getID());

            $phpTz = new DateTimeZone($id);
            $tz = $this->timeZone->getTimeZone($id);
            $iof = $this->ba->isInstanceOf($tz, 'java.util.TimeZone');
            $this->assertTrue($iof);
            $this->assertEquals($id, (string) $tz->getID());
        }
    }

    public function testGetTimezoneThrowsUnsupportedTzException()
    {
        //TimeZone.getTimeZone("GMT-8").getID() returns "GMT-08:00".
        $this->setExpectedException('Soluble\Japha\Util\Exception\UnsupportedTzException');
        $tz = $this->timeZone->getTimeZone('invalidTz');
    }

    public function testGetTimezoneThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Japha\Util\Exception\InvalidArgumentException');
        $tz = $this->timeZone->getTimeZone([0,2,3]);

    }

    public function testSetDefault()
    {
        $originalTz = $this->timeZone->getDefault();
        $ids = ['Europe/Paris', 'CET', 'GMT'];
        foreach ($ids as $id) {
            $tz = $this->timeZone->getTimeZone($id);
            $this->timeZone->setDefault($tz);
            $default = $this->timeZone->getDefault()->getID();
            $this->assertEquals($id, (string) $default);
        }
        $this->timeZone->setDefault($originalTz);
    }

    public function testGetDefaultEnableCache()
    {
        $originalTz = $this->timeZone->getDefault();

        $this->timeZone->setDefault('Europe/Paris');
        $parisTz = $this->timeZone->getDefault($enableTzCache = true)->getID();

        // native setting of a new timezone
        $newTz = $this->timeZone->getTimeZone('Europe/London');
        $this->ba->javaClass('java.util.TimeZone')->setDefault($newTz);
        $newDefault = $this->ba->javaClass('java.util.TimeZone')->getDefault()->getID();
        $this->assertEquals('Europe/London', (string) $newDefault);

        // should produce same as previous (means wrong behaviour)
        $cachedTz = $this->timeZone->getDefault($enableTzCache = true)->getID();
        $this->assertEquals((string) $parisTz, (string) $cachedTz);

        // with uncached you should have the new one
        $uncachedTz = $this->timeZone->getDefault($enableTzCache = false)->getID();
        $this->assertEquals('Europe/London', (string) $uncachedTz);

        $this->timeZone->setDefault($originalTz);
    }

    public function testGetDefaultStaticCache()
    {
        $originalTz = $this->timeZone->getDefault();

        TimeZone::disableTzCache();

        $this->timeZone->setDefault('Europe/Paris');
        $parisTz = $this->timeZone->getDefault($enableTzCache = true)->getID();

        // native setting of a new timezone
        $newTz = $this->timeZone->getTimeZone('Europe/London');
        $this->ba->javaClass('java.util.TimeZone')->setDefault($newTz);
        $newDefault = $this->ba->javaClass('java.util.TimeZone')->getDefault()->getID();
        $this->assertEquals('Europe/London', (string) $newDefault);

        // should always produce the good behaviour
        $cachedTz = $this->timeZone->getDefault($enableTzCache = true)->getID();
        $this->assertEquals('Europe/London', (string) $cachedTz);

        // with uncached you should have the new one
        $uncachedTz = $this->timeZone->getDefault($enableTzCache = false)->getID();
        $this->assertEquals('Europe/London', (string) $uncachedTz);

        $this->timeZone->setDefault($originalTz);
    }
}

<?php

namespace SolubleTest\Japha\Bridge\Driver;

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
        $hash = $ba->java('java.util.HashMap', ['my_key' => 'my_value']);
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

    public function testDateStrToTimeMilliseconds()
    {

        // Simple date milliseconds
        $ba = $this->adapter;
        $expectations = [
            '2012-12-31 23:59:59',
            '2015-01-01 00:00:00'
        ];
        $pattern = "yyyy-MM-dd HH:mm:ss";
        $simpleDateFormat = $ba->java("java.text.SimpleDateFormat", $pattern);

        foreach ($expectations as $date) {
            $phpMilli = (strtotime($date) * 1000);
            $jDate = $ba->java('java.util.Date', $phpMilli);
            $formattedDate = (string) $simpleDateFormat->format($jDate);
            $this->assertEquals($date, $formattedDate);
        }

        // When strtotime fails

        $faultyDate = "2012-12-34 23:59:59";
        $phpMilli = (strtotime($faultyDate) * 1000);
        $this->assertEquals(0, $phpMilli);
        $jDate = $ba->java('java.util.Date', $phpMilli);
        // To limit issues with different timezones
        // just check the date part
        $dateFormatter = $ba->java('java.text.SimpleDateFormat', "yyyy-MM-dd");
        $this->assertEquals('1970-01-01', (string) $dateFormatter->format($jDate));

    }

    public function testDateWithDateTime() {

        $ba = $this->adapter;
        $expectations = [
            '2012-12-31',
            '2015-01-01'
        ];

        $jDateFormatter = $ba->java("java.text.SimpleDateFormat", 'yyyy-MM-dd');

        foreach ($expectations as $value) {

            $phpDate = \DateTime::createFromFormat('Y-m-d', $value);
            $milli = $phpDate->format('U') * 1000;

            $javaDate = $ba->java('java.util.Date', $milli);
            
            $parsedJavaDate = $jDateFormatter->parse($value);

            $this->assertEquals($value, (string) $jDateFormatter->format($javaDate));
            $this->assertEquals($value, (string) $jDateFormatter->format($parsedJavaDate));

        }


    }




    public function testDateAdvanced()
    {
        $ba = $this->adapter;

        // Step 1: Check with system java timezone

        $pattern = "yyyy-MM-dd HH:mm";
        $formatter = $ba->java("java.text.SimpleDateFormat", $pattern);
        $tz = $ba->javaClass('java.util.TimeZone')->getTimezone("UTC");
        $formatter->setTimeZone($tz);

        $first = $formatter->format($ba->java("java.util.Date", 0));
        $this->assertEquals('1970-01-01 00:00', $first);

        $systemJavaTz = (string) $formatter->getTimeZone()->getId();

        $dateTime = new \DateTime(null, new \DateTimeZone($systemJavaTz));

        $now = $formatter->format($ba->java("java.util.Date"));
        $this->assertEquals($dateTime->format('Y-m-d H:i'), $now);

        // Step 2: Check with system php timezone

        $pattern = "yyyy-MM-dd HH:mm";
        $formatter = $ba->java("java.text.SimpleDateFormat", $pattern);
        $systemPhpTz  = date_default_timezone_get();
        $tz = $ba->javaClass('java.util.TimeZone')->getTimezone($systemPhpTz);
        $formatter->setTimeZone($tz);

        $dateTime = new \DateTime(null);

        $now = $formatter->format($ba->java("java.util.Date"));
        $this->assertEquals($dateTime->format('Y-m-d H:i'), $now);

        // Step 3: Different Timezones (europe/london and europe/paris -> 1 hour difference)

        $pattern = "yyyy-MM-dd HH:mm:ss";

        $formatter = $ba->java("java.text.SimpleDateFormat", $pattern);

        $phpTz = new \DateTimeZone("Europe/Paris");

        $reference_date = "2012-11-07 12:52:23";
        $phpDate  = \DateTime::createFromFormat("Y-m-d H:i:s", $reference_date, $phpTz);

        $formatter->setTimeZone($ba->javaClass('java.util.TimeZone')->getTimezone("Europe/Paris"));
        $date = $formatter->parse($reference_date);
        $formatter->setTimeZone($ba->javaClass('java.util.TimeZone')->getTimezone("Europe/London"));
        $javaDate = (string) $formatter->format($date);
        $this->assertNotEquals($phpDate->format('Y-m-d H:i:s'), $javaDate);
        $this->assertEquals($reference_date, $phpDate->format('Y-m-d H:i:s'));

        $phpDate->sub(new \DateInterval('PT1H'));
        $this->assertEquals($phpDate->format('Y-m-d H:i:s'), $javaDate);

    }
}

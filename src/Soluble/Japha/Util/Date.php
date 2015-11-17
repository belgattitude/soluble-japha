<?php

namespace Soluble\Japha\Util;

use Soluble\Japha\Bridge;
use Soluble\Japha\Interfaces;
use DateTimeZone;
use DateTime;

class Date
{

    /**
     *
     * @var Bridge\Adapter;
     */
    protected $ba;


    /**
     *
     * @var Interfaces\JavaObject
     */
    protected $timeZone;

    /**
     *
     * @var Interfaces\JavaObject Java("java.text.SimpleDateFormat")
     */
    protected $dateFormat;


    /**
     *
     * @param Bridge\Adapter $ba
     */
    public function __construct(Bridge\Adapter $ba)
    {
        $this->ba = $ba;
        $pattern = "yyyy-MM-dd HH:mm";

        //$formatter = $ba->java("java.text.SimpleDateFormat", $pattern);
        //$this->SimpleDateFormat sdf =  new SimpleDateFormat("yyyy-MM-dd HH:mm:ss Z")
        $this->timeZone = $this->ba->getSystem()->getTimeZone();
    }

    /**
     * Return java date object from a php DateTime object and optionnal timezone
     *
     *
     * @see \DateTime::createFromFormat();
     *
     * @param DateTime|null $date
     *
     * @return Interfaces\JavaObject Java('java.util.Date')
     */
    public function createDate(DateTime $date = null)
    {
        //System.currentTimeMillis()
        if ($date === null) {
            return $this->ba->java("java.util.Date");
        }





        $pattern = "yyyy-MM-dd HH:mm";
        $formatter = $ba->java("java.text.SimpleDateFormat", $pattern);
        $tz = $ba->javaClass('java.util.TimeZone')->getTimezone("GMT+0");
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



        $ba->java("java.util.Date");
    }
}

<?php

namespace Soluble\Japha\Util;

use Soluble\Japha\Bridge;
use Soluble\Japha\Interfaces;
use DateTimeZone;

class TimeZone
{
    /**
     * Whether to activate TimeZone local cache.
     *
     * @var bool
     */
    protected static $enableTzCache = true;

    /**
     * @var Bridge\Adapter;
     */
    protected $ba;

    /**
     * Cache for availableTz.
     *
     * @var array
     */
    protected $availableTz;

    /**
     * Cache for default TimeZone.
     *
     * @var Interfaces\JavaObject Java(java.util.Timezone)
     */
    protected static $defaultTz;

    /**
     * @var Interfaces\JavaClass Java(java.util.Timezone)
     */
    protected $timeZoneClass;

    /**
     * @param Bridge\Adapter $ba
     */
    public function __construct(Bridge\Adapter $ba)
    {
        $this->ba = $ba;
        $this->timeZoneClass = $ba->javaClass('java.util.TimeZone');
    }

    /**
     * Return java available timezone ids.
     *
     * @return array
     */
    public function getAvailableIDs()
    {
        if ($this->availableTz === null) {
            $this->availableTz = [];
            $available = $this->timeZoneClass->getAvailableIDs();
            foreach ($available as $id) {
                $this->availableTz[] = $id;
            }
        }

        return $this->availableTz;
    }

    /**
     * Return default jvm TimeZone.
     *
     * If TimeZone::enableTzCache() is active (by default),
     * the default JVM timezone object will be locally cached on the
     * PHP side for performance reasons.
     *
     * The behaviour could potentially lead to unexpected results if
     * your code modify the default JVM timezone during execution.
     * (i.e. by calling java.util.TimeZone::setDefault() in your code).
     *
     * If you don't want to rely on automatic timezone caching, you can
     * disable it at bootstrap (Soluble\Japha\Util\TimeZone::disableTzCache)
     * or simply call this method with $enableTzCache=false
     *
     *
     * @param bool $enableTzCache enable local caching of default timezone
     *
     * @return Interfaces\JavaObject Java(java.util.TimeZone)
     */
    public function getDefault($enableTzCache = true)
    {
        $enableCache = $enableTzCache && self::$enableTzCache;
        if (!$enableCache || self::$defaultTz === null) {
            self::$defaultTz = $this->timeZoneClass->getDefault();
        }

        return self::$defaultTz;
    }

    /**
     * Create a Java(java.util.TimeZone) object from id.
     *
     * @throws Exception\UnsupportedTzException
     * @throws \Soluble\Japha\Bridge\Exception\JavaException
     *
     * @param string|DateTimeZone $id string identifier or php DateTimeZone
     *
     * @return Interfaces\JavaObject Java('java.util.TimeZone')
     */
    public function getTimeZone($id)
    {
        if ($id instanceof DateTimeZone) {
            $id = $id->getName();
        }

        /**
         * @var $tz Interfaces\JavaClass
         */
        $tz = $this->timeZoneClass->getTimeZone($id);
        $id = (string) $tz->getID();
        if ($id == 'GMT' && $id != 'GMT') {
            $msg = "The timezone id '$id' could not be understood by JVM (JVM returned defaulted to GMT)";
            throw new Exception\UnsupportedTzException($msg);
        }

        return $tz;
    }

    /**
     * Set default JVM/servlet timezone.
     *
     * @throws \Soluble\Japha\Bridge\Exception\JavaException
     * @throws Exception\UnsupportedTzException
     *
     * @param string|Interfaces\JavaObject|DateTimeZone $timeZone timezone id, Java(java.util.Timezone) or php DateTimeZone
     */
    public function setDefault($timeZone)
    {
        if (is_string($timeZone) || $timeZone instanceof DateTimeZone) {
            $timeZone = $this->getTimeZone($timeZone);
        }
        self::$defaultTz = $timeZone;
    }

    /**
     * Enable local timezone cache.
     *
     * TimeZone::enableTzCache() enable local object caching
     * for defaultTimezone
     *
     * This behaviour could potentially lead to unexpected results if
     * your code modify the default JVM timezone during execution.
     * (i.e. by calling java.util.TimeZone::setDefault() in your code).
     *
     * If you don't want to rely on automatic timezone caching, you can
     * disable it at bootstrap (Soluble\Japha\Util\TimeZone::disableTzCache)
     */
    public static function enableTzCache()
    {
        self::$enableTzCache = true;
        self::$defaultTz = null;
    }

    /**
     * Disable local timezone cache.
     *
     * TimeZone::disbaleTzCache() disable local object caching
     * for defaultTimezone
     */
    public static function disableTzCache()
    {
        self::$enableTzCache = false;
        self::$defaultTz = null;
    }
}

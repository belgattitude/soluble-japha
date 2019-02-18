<?php

declare(strict_types=1);

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2019 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

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
     * @var Bridge\Adapter
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
     * @var Interfaces\JavaObject|null Java(java.util.Timezone)
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
    public function getAvailableIDs(): array
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
     * @return Interfaces\JavaObject Java('java.util.TimeZone')
     */
    public function getDefault($enableTzCache = true): Interfaces\JavaObject
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
     * @throws Exception\InvalidArgumentException
     * @throws \Soluble\Japha\Bridge\Exception\JavaException
     *
     * @param string|DateTimeZone $id string identifier or php DateTimeZone
     *
     * @return Interfaces\JavaObject Java('java.util.TimeZone')
     */
    public function getTimeZone($id): Interfaces\JavaObject
    {
        if ($id instanceof DateTimeZone) {
            $phpTimezone = $id->getName();
        } elseif (is_string($id) && trim($id) != '') {
            $phpTimezone = $id;
        } else {
            throw new Exception\InvalidArgumentException('Method getTimeZone($id) require argument to be datetimeZone or a non empty string');
        }

        /**
         * @var Interfaces\JavaClass
         */
        $tz = $this->timeZoneClass->getTimeZone($phpTimezone);

        /**
         * @var string
         */
        $javaTimezone = (string) $tz->getID();
        if ($javaTimezone === 'GMT' && $phpTimezone !== 'GMT') {
            $msg = sprintf(
                "The timezone id '%s' could not be understood by JVM (JVM returned defaulted to GMT)",
                $id instanceof DateTimeZone ? $id->getName() : $id
            );
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
    public function setDefault($timeZone): void
    {
        if (is_string($timeZone) || $timeZone instanceof DateTimeZone) {
            $timeZone = $this->getTimeZone($timeZone);
        }
        $this->timeZoneClass->setDefault($timeZone);
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
    public static function enableTzCache(): void
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
    public static function disableTzCache(): void
    {
        self::$enableTzCache = false;
        self::$defaultTz = null;
    }
}

<?php

declare(strict_types=1);

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace Soluble\Japha\Bridge\Adapter;

use Soluble\Japha\Bridge;
use Soluble\Japha\Interfaces;
use Soluble\Japha\Util\TimeZone;
use Soluble\Japha\Util\Exception\UnsupportedTzException;

class System
{
    /**
     * @var Bridge\Adapter
     */
    protected $ba;

    /**
     * @var TimeZone
     */
    protected $timeZone;

    /**
     * @param Bridge\Adapter $ba
     */
    public function __construct(Bridge\Adapter $ba)
    {
        $this->ba = $ba;
        $this->timeZone = new TimeZone($ba);
    }

    /**
     * Get php DateTime helper object.
     *
     * @return TimeZone
     */
    public function getTimeZone(): TimeZone
    {
        return $this->timeZone;
    }

    /**
     * Return system default timezone id.
     *
     * @return string
     */
    public function getTimeZoneId(): string
    {
        return (string) $this->timeZone->getDefault()->getID();
    }

    /**
     * Set system default timezone.
     *
     * @throws UnsupportedTzException
     *
     * @param string|Interfaces\JavaObject|\DateTimeZone $timezone timezone id, Java(java.util.Timezone) or php DateTimeZone
     */
    public function setTimeZoneId($timezone): void
    {
        $this->timeZone->setDefault($timezone);
    }
}

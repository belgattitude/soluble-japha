<?php

declare(strict_types=1);

namespace Soluble\Japha\Bridge\Driver\Pjb62\Utils;

/**
 * @author Vanvelthem Sébastien
 */
class HelperFunctions
{
    /**
     * @return int
     */
    public static function java_get_session_lifetime(): int
    {
        $session_max_lifetime = ini_get('session.gc_maxlifetime');

        return $session_max_lifetime ? (int) $session_max_lifetime : 1440;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public static function java_truncate(string $str): string
    {
        if (strlen($str) > 955) {
            return substr($str, 0, 475).'[...]'.substr($str, -475);
        }

        return $str;
    }
}

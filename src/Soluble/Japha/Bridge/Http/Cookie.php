<?php

declare(strict_types=1);

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace Soluble\Japha\Bridge\Http;

/**
 * Utility class to serialize a PHP cookie array into a valid HTTP cookie header.
 */
class Cookie
{
    /**
     * Value used for values that cannot be serialized.
     */
    public const UNSUPPORTED_TYPE_VALUE = '__UNSUPPORTED_TYPE__';

    /**
     * Serialize PHP's $_COOKIE values into a valid HTTP COOKIE string.
     *
     * @param array $cookies if null
     *
     * @return string|null
     */
    public static function getCookiesHeaderLine(array $cookies = null): ?string
    {
        $cookies = $cookies ?? $_COOKIE;
        if (empty($cookies)) {
            return null;
        }

        $cookieParts = [];
        foreach ($cookies as $k => $v) {
            $cookieParts[] = self::serializePHPCookies($k, $v);
        }

        return 'Cookie: '.implode(';', $cookieParts);
    }

    /**
     * Escapes $cookieValue taking into account its type to serialize it as a valid cookie value.
     *
     * @param string $cookieName
     * @param mixed  $cookieValue
     *
     * @return string
     */
    private static function serializePHPCookies(string $cookieName, $cookieValue): string
    {
        $cookieParts = [];
        $urlEncodedCookieName = urlencode($cookieName);
        $valueType = gettype($cookieValue);
        switch ($valueType) {
            case 'integer':
            case 'double':
            case 'string':
                $urlEncodedCookieValue = urlencode((string) $cookieValue);
                $cookieParts[] = "$urlEncodedCookieName=$urlEncodedCookieValue";
                break;

            case 'array':
                foreach ($cookieValue as $cookieValueKey => $cookieValueValue) {
                    $cookieParts[] = self::serializePHPCookies($cookieName."[$cookieValueKey]", $cookieValueValue);
                }
                break;

            case 'NULL':
                $cookieParts[] = "$urlEncodedCookieName=";
                break;

            case 'boolean':
                $cookieParts[] = "$urlEncodedCookieName=".((bool) $cookieValue ? '1' : '0');
                break;

            // It's a security risk to serialize an object and send it as a cookie
            case 'object':
                // Intentional fallthrough
            default:
                $cookieParts[] = "$urlEncodedCookieName=".self::UNSUPPORTED_TYPE_VALUE;
                break;
        }

        return implode(';', $cookieParts);
    }
}

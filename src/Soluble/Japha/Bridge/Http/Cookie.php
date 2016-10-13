<?php

namespace Soluble\Japha\Bridge\Http;

/**
 * Utility class to serialize a PHP cookie array into a valid HTTP cookie header
 */
class Cookie
{
    /**
     * Value used for values that cannot be serialized
     */
    const UNSUPPORTED_TYPE_VALUE = '__UNSUPPORTED_TYPE__';

    /**
     * Serialize PHP's $_COOKIE values into a valid HTTP COOKIE string
     *
     * @param array $cookies -  Usually PHP's superglobal $_COOKIE
     * @return string
     */
    static public function getCookiesHeaderLine(array $cookies)
    {
        $cookieParts = [];
        foreach($cookies as $k => $v) {
            $cookieParts[] = self::serializePHPCookies($k, $v);
        }

        if (!count($cookieParts)) {
            return '';
        }

        $fullCookieString = 'Cookie: ' . implode(';', $cookieParts) . "\r\n";

        return  $fullCookieString;
    }

    /**
     * Escapes $cookieValue taking into account its type to serialize it as a valid cookie value
     *
     * @param string $cookieName
     * @param mixed $cookieValue
     *
     * @return string
     */
    private static function serializePHPCookies($cookieName, $cookieValue)
    {

        $urlEncodedCookieName = urlencode($cookieName);
        $valueType = gettype($cookieValue);
        switch($valueType) {

            case 'integer':
            case 'double':
            case 'string':
                $urlEncodedCookieValue = urlencode($cookieValue);
                $cookieParts[] = "$urlEncodedCookieName=$urlEncodedCookieValue";
                break;

            case 'array':
                foreach($cookieValue as $cookieValueKey => $cookieValueValue) {
                    $cookieParts[] = self::serializePHPCookies($cookieName . "[$cookieValueKey]", $cookieValueValue);
                }
                break;

            case 'NULL':
                $cookieParts[] = "$urlEncodedCookieName=";
                break;

            case 'boolean':
                $cookieParts[] = "$urlEncodedCookieName=" . ($cookieValue ? '1' : '0');
                break;

            // It's a security risk to serialize an object and send it as a cookie
            case 'object':
                // Intentional fallthrough
            default:
                $cookieParts[] = "$urlEncodedCookieName=" . self::UNSUPPORTED_TYPE_VALUE;
                break;
        }

        return implode(';', $cookieParts);
    }

}
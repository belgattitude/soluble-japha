<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Http;

use Soluble\Japha\Bridge\Http\Cookie;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    public function testNullHeaderLine()
    {
        self::assertNull(Cookie::getCookiesHeaderLine([]));
    }

    /**
     * @dataProvider cookiesProvider
     */
    public function testGetCookiesHeaderLine(array $cookies, $expectedString)
    {
        $expectedString = "Cookie: $expectedString";
        $cookieString = Cookie::getCookiesHeaderLine($cookies);

        $urlDecodedString = urldecode($cookieString);

        self::assertSame($expectedString, $urlDecodedString, 'test that cookie was correctly serialized');
    }

    public function cookiesProvider(): array
    {
        return [
            // scenario: single scalar
            [
                // Original cookies
                [
                    'cookieName' => 'cookieValue'
                ],
                // Serialized string
                'cookieName=cookieValue'
            ],

            // scenario: two scalars
            [
                // Original cookies
                [
                    'stringCookie' => 'cookieValue',
                    'integerCookie' => 123,
                ],
                // Serialized string
                'stringCookie=cookieValue;integerCookie=123'
            ],

            // scenario: booleans and null
            [
                // Original cookies
                [
                    'booleanCookieFalse' => false,
                    'booleanCookieTrue' => true,
                    'nullCookie' => null
                ],
                // Serialized string
                'booleanCookieFalse=0;booleanCookieTrue=1;nullCookie='
            ],

            // scenario: complex array
            [
                // Original cookies
                [
                    'complexArrayCookie' => [
                        'firstNumericItem' => 1,
                        'secondBooleanItem' => false,
                        'thirdNullItem' => null,
                        'fourthArrayItem' => [
                            1,      // index 0
                            'two',  // index 1
                            true,   // index 2
                            ['ABC'], // index 3
                            'key' => 'value' // index 'key',
                        ]
                    ]
                ],
                // Serialized string
                'complexArrayCookie[firstNumericItem]=1;'
                .'complexArrayCookie[secondBooleanItem]=0;'
                .'complexArrayCookie[thirdNullItem]=;'
                .'complexArrayCookie[fourthArrayItem][0]=1;'
                .'complexArrayCookie[fourthArrayItem][1]=two;'
                .'complexArrayCookie[fourthArrayItem][2]=1;'
                .'complexArrayCookie[fourthArrayItem][3][0]=ABC;'
                .'complexArrayCookie[fourthArrayItem][key]=value'
            ],

            // scenario: unsupported types
            [
                // Original cookies
                [
                    'dateTimeObject' => new \DateTime(),
                    'function' => function () {
                    },
                ],
                // Serialized string
                'dateTimeObject='.Cookie::UNSUPPORTED_TYPE_VALUE.';function='.Cookie::UNSUPPORTED_TYPE_VALUE
            ],
        ];
    }
}

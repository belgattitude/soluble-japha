<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\Java;
use Soluble\Japha\Bridge\Exception\JavaException;
use PHPUnit\Framework\TestCase;

class JavaTest extends TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
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
    protected function tearDown(): void
    {
    }

    public function testConstructArgTypeNull()
    {
        try {
            $ret = new Java('java.lang.String', null);
            self::assertFalse(true, 'Should throw a NullPointerException');
        } catch (JavaException $e) {
            self::assertStringContainsString('NullPointerException', $e->getMessage());
        }
    }

    public function testConstructArgTypeResource()
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, 'COOL');
        $str = new Java('java.lang.String', $tmpFile);
        self::assertEquals('', (string) $str);
    }

    public function testConstructArgTypePHPObject()
    {
        $this->expectException(JavaException::class);
        $str = new Java('java.lang.String', new \stdClass());
    }
}

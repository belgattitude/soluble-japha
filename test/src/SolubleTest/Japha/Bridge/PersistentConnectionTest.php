<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\Client;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;
use PHPUnit\Framework\TestCase;

class PersistentConnectionTest extends TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        \SolubleTestFactories::startJavaBridgeServer();
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    public function testConstructorParamUsePersitentConnection(): void
    {
        PjbProxyClient::unregisterInstance();
        $ba = new Adapter([
            'servlet_address' => $this->servlet_address,
            'use_persistent_connection' => true
        ]);

        /** @var $client PjbProxyClient */
        $client = $ba->getDriver()->getClient();
        $this->assertTrue($client->getOption('use_persistent_connection'));
        $this->assertTrue(PjbProxyClient::getInstance()::getClient()->getParam(Client::PARAM_USE_PERSISTENT_CONNECTION));
        PjbProxyClient::unregisterInstance();
    }

    public function testConstructorParamUseNonPersitentConnection(): void
    {
        PjbProxyClient::unregisterInstance();
        $ba = new Adapter([
            'servlet_address' => $this->servlet_address,
            'use_persistent_connection' => false
        ]);

        /** @var $client PjbProxyClient */
        $client = $ba->getDriver()->getClient();
        $this->assertFalse($client->getOption('use_persistent_connection'));
        $this->assertFalse(PjbProxyClient::getInstance()::getClient()->getParam(Client::PARAM_USE_PERSISTENT_CONNECTION));
        PjbProxyClient::unregisterInstance();
    }
}

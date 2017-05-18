<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Db;

use Soluble\Japha\Bridge\Adapter as Adapter;
use Soluble\Japha\Db\DriverManager;

class DriverManagerTest extends \PHPUnit_Framework_TestCase
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
     * @var DriverManager
     */
    protected $driverManager;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!$this->isJdbcTestsEnabled()) {
            $this->markTestSkipped(
                'Skipping JDBC mysql driver tests, enable option in phpunit.xml'
            );
        }

        \SolubleTestFactories::startJavaBridgeServer();
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->adapter = new Adapter([
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ]);
        $this->driverManager = new DriverManager($this->adapter);
    }

    protected function isJdbcTestsEnabled()
    {
        return isset($_SERVER['JAPHA_ENABLE_JDBC_TESTS']) &&
            $_SERVER['JAPHA_ENABLE_JDBC_TESTS'] == true;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testCreateConnectionThrowsClassNotFoundException()
    {
        $this->setExpectedException('Soluble\Japha\Bridge\Exception\ClassNotFoundException');
        //$this->driverManager->createConnection()
        $config = \SolubleTestFactories::getDatabaseConfig();
        $host = $config['hostname'];
        $db = $config['database'];
        $user = $config['username'];
        $password = $config['password'];
        $dsn = "jdbc:mysql://$host/$db?user=$user&password=$password";
        $conn = $this->driverManager->createConnection($dsn, 'com.nuvolia.jdbc.JDBC4Connection');
    }

    public function testCreateConnectionThrowsSqlException()
    {
        $this->setExpectedException('Soluble\Japha\Bridge\Exception\SqlException');
        //$this->driverManager->createConnection()
        $config = \SolubleTestFactories::getDatabaseConfig();
        $host = $config['hostname'];
        $db = $config['database'];
        $user = $config['username'];
        $password = $config['password'];
        $dsn = "jdbc:invaliddbdriver://$host/$db?user=$user&password=$password";
        $conn = $this->driverManager->createConnection($dsn, 'com.mysql.jdbc.Driver');
    }

    public function testCreateConnectionThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Japha\Bridge\Exception\InvalidArgumentException');
        $dsn = '';
        $conn = $this->driverManager->createConnection($dsn, 'com.nuvolia.jdbc.JDBC4Connection');
    }

    public function testGetDriverManager()
    {
        $dm = $this->driverManager->getDriverManager();
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $dm);
        $className = $this->adapter->getDriver()->getClassName($dm);
        $this->assertEquals('java.sql.DriverManager', $className);
        //$this->assertTrue($this->adapter->isInstanceOf($dm, 'java.sql.DriverManager'));
    }

    public function testCreateConnection()
    {
        $dsn = $this->getWorkingDSN();
        try {
            $conn = $this->driverManager->createConnection($dsn);
        } catch (\Exception $e) {
            $this->assertFalse(true, 'Cannot connect: ' . $e->getMessage());
        }
        $className = $this->adapter->getDriver()->getClassName($conn);
        $this->assertTrue(in_array($className, ['com.mysql.jdbc.JDBC4Connection', 'com.mysql.cj.jdbc.ConnectionImpl']));
        $conn->close();
    }

    public function testStatement()
    {
        $dsn = $this->getWorkingDSN();
        try {
            $conn = $this->driverManager->createConnection($dsn);
        } catch (\Exception $e) {
            $this->assertFalse(true, 'Cannot connect: ' . $e->getMessage());
        }

        $stmt = $conn->createStatement();
        $rs = $stmt->executeQuery('select * from product_category_translation limit 100');
        while ($rs->next()) {
            $category_id = $rs->getString('category_id');
            $this->assertInternalType('numeric', $category_id->__toString());
        }
        $ba = $this->adapter;
        if (!$ba->isNull($rs)) {
            $rs->close();
        }
        if (!$ba->isNull($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }

    public function testInvalidQueryThrowsException()
    {
        $this->expectException(\Soluble\Japha\Bridge\Exception\JavaException::class);
        $dsn = $this->getWorkingDSN();
        try {
            $conn = $this->driverManager->createConnection($dsn);
        } catch (\Exception $e) {
            $this->assertFalse(true, 'Cannot connect: ' . $e->getMessage());
        }

        $stmt = $conn->createStatement();
        try {
            $rs = $stmt->executeQuery('select * from non_existing_table limit 100');
            $this->assertTrue(false, 'Error: a JavaException exception was expected');
        } catch (\Soluble\Japha\Bridge\Exception\JavaException $e) {
            $this->assertTrue(true, 'Exception have been thrown');
            $java_cls = $e->getJavaClassName();
            $this->assertTrue(in_array($java_cls, [
                'com.mysql.jdbc.exceptions.jdbc4.MySQLSyntaxErrorException',
                'java.sql.SQLSyntaxErrorException'
            ]));
            $conn->close();
            throw $e;
        }
    }

    public function testGetJdbcDSN()
    {
        $dsn = DriverManager::getJdbcDsn('mysql', 'db', 'host', 'user', 'password', []);
        $this->assertEquals('jdbc:mysql://host/db?user=user&password=password', $dsn);
    }

    public function testGetJdbcDSNWithExtras()
    {
        $extras = [
          'param1' => 'Hello',
          'param2' => 'éà&AA'
        ];
        $dsn = DriverManager::getJdbcDsn('mysql', 'db', 'host', 'user', 'password', $extras);
        $expected = 'jdbc:mysql://host/db?user=user&password=password&param1=Hello&param2=%C3%A9%C3%A0%26AA';
        $this->assertEquals($expected, $dsn);
    }

    protected function getWorkingDSN()
    {
        $config = \SolubleTestFactories::getDatabaseConfig();
        $host = $config['hostname'];
        $db = $config['database'];
        $user = $config['username'];
        $password = $config['password'];
        $serverTimezone = urlencode('GMT+1');
        $dsn = "jdbc:mysql://$host/$db?user=$user&password=$password&serverTimezone=$serverTimezone";

        return $dsn;
    }
}

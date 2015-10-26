<?php

namespace Soluble\Japha\Db;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Exception;

class DriverManager
{
    /**
     *
     * @Java(java.sql.DriverManager)
     */
    protected $driverManager;

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     *
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Create an sql connection to database
     *
     *
     * @throws Exception\JavaException
     * @throws Exception\InvalidArgumentException
     *
     * @param string $dsn
     * @param string $driverClass
     * @return Java(java.sql.Connection)
     */
    public function createConnection($dsn, $driverClass = 'com.mysql.jdbc.Driver')
    {
        if (!is_string($dsn) || trim($dsn) == '') {
            $message = "DSN param must be a valid (on-empty) string";
            throw new Exception\InvalidArgumentException(__METHOD__ . ' ' . $message);
        }

        $class = $this->adapter->javaClass("java.lang.Class");
        try {
            $class->forName($driverClass);
        } catch (\Exception $e) {
            // Here testing class not found error
            $message = "Class not found '$driverClass' exception";
            throw new Exception\ClassNotFoundException(__METHOD__ . ' ' . $message, $code = null, $e);
        }

        try {
            $conn = $this->getDriverManager()->getConnection($dsn);
        } catch (Exception\JavaExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            $message = "Unexpected exception thrown with message " . $e->getMessage();
            throw new Exception\UnexpectedException(__METHOD__ . ' ' . $message);
        }
        return $conn;
    }

    /**
     * Return underlying java driver manager
     *
     * @return Java(java.sql.DriverManager)
     */
    public function getDriverManager()
    {
        if ($this->driverManager === null) {
            $this->driverManager = $this->adapter->javaClass('java.sql.DriverManager');
        }
        return $this->driverManager;
    }

    /**
     * Return a JDBC DSN string
     *
     * @param string $db database name
     * @param string $host server ip or name
     * @param string $user username to connect
     * @param string $password password to connect
     * @param string $driverType diverType (mysql/oracle/postgres...)
     *
     * @return string
     */
    public static function getJdbcDsn($db, $host, $user, $password, $driverType = 'mysql')
    {
        return "jdbc:$driverType://$host/$db?user=$user&password=$password";
    }
}

<?php

namespace Soluble\Japha\Db;

use Soluble\Japha\Bridge\PhpJavaBridge as Pjb;

class DriverManager
{
    
    /**
     *
     * @Java(java.sql.DriverManager)
     */
    protected $driverManager;
    

    public function __construct()
    {
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

    /**
     * Return JDBC dsn from doctrine connection
     *
     * @param Doctrine_Connection $conn if null takes the current opened connection
     * @param string $driverType default to mysql
     * @return string i.e jdbc:mysql://localhost/dbname?user=root&password=mypassword
     */
    public static function getJdbcDsnFromDoctrine(Doctrine_Connection $conn = null, $driverType = 'mysql')
    {
        if ($conn === null) {
            $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        }
        if ($driverType != 'mysql') {
            throw new Vision_Exception("Currently only mysql is supported");
        }
        $opts = new Vision_Doctrine_Connection_Options($conn);
        return self::getJdbcDsn(
            $opts->getDatabase(),
            $opts->getHost(),
            $opts->getUsername(),
            $opts->getPassword(),
            $driverType
        );
    }
    

    /**
     * Create an sql connection to database
     *
     * @param string $dsn
     * @param string $driverClass
     * @return Java(java.sql.Connection)
     */
    public function createConnection($dsn, $driverClass = 'com.mysql.jdbc.Driver')
    {
        $class = Pjb::getJavaClass("java.lang.Class");
        $c = Pjb::getDriver()->getClassName($class);
        try {
            $class->forName($driverClass);
        } catch (\Exception $e) {
            // Here testing class not found error
            var_dump($e->__toString());
            die();
            $a = Pjb::getDriver()->inspect($e);
            var_dump($a);
            die();
        }
        
        try {
            $conn = $this->getDriverManager()->getConnection($dsn);
        } catch (\Exception $e) {
            var_dump($e->__toString());
            die();
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
            $this->driverManager = Pjb::getJavaClass('java.sql.DriverManager');
        }
        return $this->driverManager;
    }
}

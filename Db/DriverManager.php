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
     * Create an sql connection to database
     *
     * @param string $dsn
     * @param string $driverClass
     * @return Java(java.sql.Connection)
     */
    public function createConnection($dsn, $driverClass = 'com.mysql.jdbc.Driver')
    {
        /*
        $class = Pjb::getJavaClass("java.io.System");
        $c = Pjb::getDriver()->getClassName($class);
        var_dump($c);
        echo $class->getName();
        die();
        */
        $class = Pjb::getJavaClass("java.lang.Class");
        try {
            $class->forName($driverClass);
        } catch (\Exception $e) {
            // Here testing class not found error
            /*
            var_dump(get_class($e));
            die();
            
            echo "\n\n";
            echo $e->getClass();
            echo "\n\n";
            echo $e->toString();
            echo "\n\n";
            echo $e->getCause();
            
            die();
            echo \Soluble\Japha\Bridge\Driver\Pjb621\java_inspect($e);
            die();
            echo \Soluble\Japha\Bridge\Driver\Pjb621\java_values($e);
            dump($e);
            die();
            var_dump($e->__toString());
            die();
            $a = Pjb::getDriver()->inspect($e);
            var_dump($a);
            die();
             *
             */
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

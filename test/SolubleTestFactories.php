<?php

use Zend\Db\Adapter\Adapter;
use Zend\Cache\StorageFactory;
use Soluble\Normalist\Synthetic\TableManager;
use Soluble\Normalist\Driver;
use Symfony\Component\Process\Process;

class SolubleTestFactories
{
    /**
     *
     * @var boolean
     */
    protected static $javaBridgeServerStarted = false;

    /**
     *
     * @var int
     */
    // protected static $javaBridgeServerPid;

    /**
     * Start (and eventually install) the standalone
     * java bridge server
     */
    public static function startJavaBridgeServer()
    {
        
        //define("JAVA_SERVLET", "/JavaBridge/java/servlet.phpjavabridge");

        //define("JAVA_HOSTS", "localhost:8080");
        //define("JAVA_SERVLET", "/JavaBridge/java");        

        //return;

        if (!self::$javaBridgeServerStarted) {
            
            /*
            <server name="PJB_URL" value="127.0.0.1:8080" />
            <server name="PJB_SERVLET" value="true" />
            <server name="PJB_SERVLET_ADDRESS" value="/javabridge-bundle/java/servlet.phpjavabridge" />
            */
        
            if (isset($_SERVER['PJB_SERVLET']) && $_SERVER['PJB_SERVLET'] == 'true') {
                self::$javaBridgeServerStarted = true;
            } else {
                self::$javaBridgeServerStarted = true;
                
                // First ensure php java bridge is installed
                $test_dir = dirname(__FILE__);
                passthru("/bin/bash $test_dir/tools/pjb_standalone_install/install_pjb621.sh");

                $jar_file = "$test_dir/tools/pjb_standalone_install/pjb621/WEB-INF/lib/JavaBridge.jar";

                if (!file_exists($jar_file)) {
                    throw new \Exception(__METHOD__ . " Standalone javabridge install failed, see tests/tools/install_Pjb621.sh script ($jar_file)");
                }


                $url = $_SERVER['PJB_URL'];
                $tmp = explode(':', $url);
                $port = $tmp[1];


                $jar_dir = dirname($jar_file);

                $command = "java -cp $jar_dir/mysql-connector-java-5.1.36-bin.jar:$jar_file php.java.bridge.Standalone SERVLET:$port > $test_dir/logs/pjb-error.log 2>&1 &";
                echo "\nRunning pjb server: $command\n";
                echo "See logs in : $test_dir/logs/pjb-error.log\n\n";

                passthru($command);

                
                // let time for server to start

                if (preg_match('/travis/', dirname(__FILE__))) {
                    sleep(8);
                } else {
                    sleep(1);
                }
            }
        }
    }
    
    

    /**
     *
     * @return string
     */
    public static function getJavaBridgeServerAddress()
    {
        return $_SERVER['PJB_URL'] . $_SERVER['PJB_SERVLET_ADDRESS'];
    }


    /**
     * @return string
     */
    public static function getCachePath()
    {
        $cache_dir = $_SERVER['PHPUNIT_CACHE_DIR'];
        if (!preg_match('/^\//', $cache_dir)) {
            $cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cache_dir;
        }
        return $cache_dir;
    }
    
    public static function getDatabaseConfig()
    {
        $mysql_config = array();
        $mysql_config['hostname'] = $_SERVER['MYSQL_HOSTNAME'];
        $mysql_config['username'] = $_SERVER['MYSQL_USERNAME'];
        $mysql_config['password'] = $_SERVER['MYSQL_PASSWORD'];
        $mysql_config['database'] = $_SERVER['MYSQL_DATABASE'];
        $mysql_config['driver_options'] = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            );
        $mysql_config['options'] = array(
            'buffer_results' => true
        );
        $mysql_config['charset'] = 'UTF8';
        
        return $mysql_config;
    }
}

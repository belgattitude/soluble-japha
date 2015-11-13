<?php

use Zend\Db\Adapter\Adapter;
use Zend\Cache\StorageFactory;
use Soluble\Normalist\Synthetic\TableManager;
use Soluble\Normalist\Driver;
use Symfony\Component\Process\Process;

class SolubleTestFactories {

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
    public static function startJavaBridgeServer() {

        if (!self::$javaBridgeServerStarted) {

            if ($_SERVER['AUTORUN_PJB_STANDALONE'] == 'true') {

                // Step 1: installing standalone PJB
                $test_dir = dirname(__FILE__);
                passthru("/bin/bash $test_dir/tools/pjb_standalone_install/install_pjb621.sh");

                // Step 2: Locating the runnable jar file.
                $jar_file = "$test_dir/tools/pjb_standalone_install/pjb621/WEB-INF/lib/JavaBridge.jar";
                if (!file_exists($jar_file)) {
                    throw new \Exception(__METHOD__ . " Standalone javabridge install failed, see tests/tools/install_Pjb621.sh script ($jar_file)");
                }

                // Step 3: starting the standalone server
                $server_address = self::getJavaBridgeServerAddress();

                $url  = parse_url($server_address, PHP_URL_HOST);
                $port = parse_url($server_address, PHP_URL_PORT); 

                $jar_dir = dirname($jar_file);

                
                //$command = "java -cp $jar_dir/mysql-connector-java-5.1.36-bin.jar:$jar_file php.java.bridge.Standalone SERVLET:$port > $test_dir/logs/pjb-error.log 2>&1 &";
                $command = "java -cp $jar_dir/mysql-connector-java-5.1.36-bin.jar:$jar_file php.java.bridge.Standalone SERVLET:$port";
                $error_file = "$test_dir/logs/pjb-error.log";
                $pid_file   = "$test_dir/logs/pjb-standalone.pid";
                
                if (!self::isStandaloneServerRunning($pid_file)) {
                
                    echo "\nStartin standalone pjb server:\n $command\n";
                    echo "@see logs in     : $error_file\n";
                    echo "@see pid file in : $pid_file\n";

                    $cmd = sprintf("%s > %s 2>&1 & echo $! > %s", $command, $error_file, $pid_file);
                    exec($cmd);                

                    // let time for server to start
                    if (preg_match('/travis/', dirname(__FILE__))) {
                        sleep(8);
                    } else {
                        sleep(1);
                    }
                } else {
                    echo "Standalone server already running, skipping start\n";
                } 
                
                register_shutdown_function(array(__CLASS__, 'killStandaloneServer'));
            }

            self::$javaBridgeServerStarted = true;
        }
    }

    public static function killStandaloneServer()
    {
        if (self::isStandaloneServerRunning()) {
            // Just to ensure, shutdown of the client has been done before
            // killing the server
            Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient::unregisterInstance();
            
            $test_dir = dirname(__FILE__);
            $pid_file  = "$test_dir/logs/pjb-standalone.pid";
            $pid = trim(file_get_contents($pid_file));
            echo "Shutdown: killing standalone server ($pid) \n";
            $cmd = "kill $pid";
            exec($cmd);
        }
        
    }
    
    public static function isStandaloneServerRunning() {
        $test_dir = dirname(__FILE__);
        $pid_file  = "$test_dir/logs/pjb-standalone.pid";
        if (file_exists($pid_file)) {
            $pid = trim(file_get_contents($pid_file));
            if (is_numeric($pid)) {
                $result = shell_exec(sprintf("ps %d", $pid));
                if( count(preg_split("/\n/", $result)) > 2){
                    return true;
                }                    
            }
        }
        return false;
    }
    
    /**
     *
     * @return string
     */
    public static function getJavaBridgeServerAddress() {
        return $_SERVER['PJB_SERVLET_ADDRESS'];
    }

    /**
     * @return string
     */
    public static function getCachePath() {
        $cache_dir = $_SERVER['PHPUNIT_CACHE_DIR'];
        if (!preg_match('/^\//', $cache_dir)) {
            $cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cache_dir;
        }
        return $cache_dir;
    }

    public static function getDatabaseConfig() {
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

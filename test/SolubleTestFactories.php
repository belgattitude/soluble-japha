<?php

use Zend\Db\Adapter\Adapter;
use Zend\Cache\StorageFactory;
use Soluble\Normalist\Synthetic\TableManager;
use Soluble\Normalist\Driver;
use Symfony\Component\Process\Process;
use PjbServer\Tools\StandaloneServer;

class SolubleTestFactories {

    /**
     *
     * @var StandaloneServer|null
     */
    protected static $standaloneServer;

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

        
        if ($_SERVER['AUTORUN_PJB_STANDALONE'] == 'true') {

            if (self::$standaloneServer === null) {

                $server_address = self::getJavaBridgeServerAddress();
                //$url = parse_url($server_address, PHP_URL_HOST);
                $port = parse_url($server_address, PHP_URL_PORT);

                $options = array(
                    'port' => $port
                );

                try {
                    self::$standaloneServer = new StandaloneServer($options);
                    self::$standaloneServer->start();
                    //$output = self::$standaloneServer->getOutput();
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
                
                register_shutdown_function(array(__CLASS__, 'killStandaloneServer'));
            }
        }
    }

    public static function killStandaloneServer() {
        if (self::$standaloneServer !== null) {
            if (self::$standaloneServer->isRunning()) {
                self::$standaloneServer->stop();
            }
        }
    }

    public static function isStandaloneServerRunning() {
        $test_dir = dirname(__FILE__);
        $pid_file = "$test_dir/logs/pjb-standalone.pid";
        if (file_exists($pid_file)) {
            $pid = trim(file_get_contents($pid_file));
            if (is_numeric($pid)) {
                $result = shell_exec(sprintf("ps %d", $pid));
                if (count(preg_split("/\n/", $result)) > 2) {
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

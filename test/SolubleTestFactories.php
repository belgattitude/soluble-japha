<?php

use PjbServer\Tools\StandaloneServer;

class SolubleTestFactories
{

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
    public static function startJavaBridgeServer()
    {
        if ($_SERVER['AUTORUN_PJB_STANDALONE'] == 'true') {
            if (self::$standaloneServer === null) {
                $server_address = self::getJavaBridgeServerAddress();
                //$url = parse_url($server_address, PHP_URL_HOST);
                $port = parse_url($server_address, PHP_URL_PORT);

                $params = [
                    'port' => $port,
                    'classpaths' => [
                        //__DIR__ . '/resources/mysql-connector-java-5.1.36-bin.jar',
                        __DIR__ . '/resources/*.jar'
                    ]
                ];

                $config = new StandaloneServer\Config($params);

                try {
                    self::$standaloneServer = new StandaloneServer($config);
                    self::$standaloneServer->start();
                    //$output = self::$standaloneServer->getOutput();
                } catch (\Exception $e) {
                    die($e->getMessage());
                }

                register_shutdown_function([__CLASS__, 'killStandaloneServer']);
            }
        }
    }

    public static function killStandaloneServer()
    {
        if (self::$standaloneServer !== null) {
            self::$standaloneServer->stop();
        }
    }

    /**
     *
     * @return string
     */
    public static function getJavaBridgeServerAddress()
    {
        return $_SERVER['PJB_SERVLET_ADDRESS'];
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
        $mysql_config = [];
        $mysql_config['hostname'] = $_SERVER['MYSQL_HOSTNAME'];
        $mysql_config['username'] = $_SERVER['MYSQL_USERNAME'];
        $mysql_config['password'] = $_SERVER['MYSQL_PASSWORD'];
        $mysql_config['database'] = $_SERVER['MYSQL_DATABASE'];
        $mysql_config['driver_options'] = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        ];
        $mysql_config['options'] = [
            'buffer_results' => true
        ];
        $mysql_config['charset'] = 'UTF8';

        return $mysql_config;
    }
}

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
            // First ensure php java bridge is installed
            $test_dir = dirname(__FILE__);
            passthru("/bin/bash $test_dir/tools/pjb_standalone_install/install_pjb621.sh");

            $jar_file = "$test_dir/tools/pjb_standalone_install/pjb621/WEB-INF/lib/JavaBridge.jar";

            if (!file_exists($jar_file)) {
                throw new \Exception(__METHOD__ . " Standalone javabridge install failed, see tests/tools/install_pjb621.sh script ($jar_file)");
            }


            $url = self::getJavaBridgeServerAddress();
            $tmp = explode(':', $url);
            $port = $tmp[1];


            $jar_dir = dirname($jar_file);

            //java -cp /web/www/solublecomponents/tests/tools/pjb_standalone_install/pjb621/WEB-INF/lib/mysql-connector-java-5.1.34-bin.jar:/web/www/solublecomponents/tests/tools/pjb_standalone_install/pjb621/WEB-INF/lib/JavaBridge.jar php.java.bridge.Standalone SERVLET:8083
            //$command = "java  -jar $jar_file SERVLET:$port > $test_dir/logs/pjb-error.log 2>&1 &";
            $command = "java -cp $jar_dir/mysql-connector-java-5.1.35-bin.jar:$jar_file php.java.bridge.Standalone SERVLET:$port > $test_dir/logs/pjb-error.log 2>&1 &";
            echo "\nRunning pjb server: $command\n";
            echo "See logs in : $test_dir/logs/pbj-error.log\n\n";

            passthru($command);

            // let time for server to start

            if (preg_match('/travis/', dirname(__FILE__))) {
                sleep(8);
            } else {
                sleep(1);
            }
        }
        self::$javaBridgeServerStarted = true;
    }

    /**
     *
     * @return string
     */
    public static function getJavaBridgeServerAddress() {
        return $_SERVER['PJB_URL'];
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

}

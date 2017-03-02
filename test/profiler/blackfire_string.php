<?php
/**
 * PHPJavaBridge - test for blackfire profiler.
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

try {
    $ba = new BridgeAdapter([
        'driver' => 'Pjb62',
        'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge'
        //'servlet_address' => 'localhost:8080/JavaBridgeSpringboot/servlet.phpjavabridge'
    ]);
} catch (\Exception $e) {
    die('Error connecting: ' . $e->getMessage());
}

$str = $ba->java('java.lang.String', 'A very special string');

echo $str;
echo 'Profiled, see the blackfire.io profile';

<?php

require __DIR__ . '/../bootstrap.php';

if ($_SERVER['argc'] != 3) {
    echo 'This scripts must be run with 2 arguments:' . PHP_EOL;
    echo ' ./set_thread_timezone.php <servlet_address> <timezone>' . PHP_EOL;
    exit(1);
}

$servlet_address = $_SERVER['argv'][1];
$timezone = $_SERVER['argv'][2];

$ba = new \Soluble\Japha\Bridge\Adapter([
    'servlet_address' => $servlet_address,
    'driver' => 'Pjb62',
    'java_default_timezone' => $timezone
]);

$tz = new \Soluble\Japha\Util\TimeZone($ba);
//$tz->setDefault($timezone);
echo (string) $tz->getDefault(false)->getID();

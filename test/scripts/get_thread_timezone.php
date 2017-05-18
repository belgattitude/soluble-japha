<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

require __DIR__ . '/../bootstrap.php';

if ($_SERVER['argc'] != 2) {
    echo 'This scripts must be run with 2 arguments:' . PHP_EOL;
    echo ' ./get_thread_timezone.php <servlet_address>' . PHP_EOL;

    exit(1);
}

$servlet_address = $_SERVER['argv'][1];

$ba = new \Soluble\Japha\Bridge\Adapter([
    'servlet_address' => $servlet_address,
    'driver' => 'Pjb62'
]);

$tz = new \Soluble\Japha\Util\TimeZone($ba);
echo (string) $tz->getDefault(false)->getID();

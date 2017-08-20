<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

if (!$loader = @include __DIR__ . '/../vendor/autoload.php') {
    die('You must set up the project dependencies, run the following commands:' . PHP_EOL .
            'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
            'php composer.phar install' . PHP_EOL);
}

ini_set('display_errors', 'true');
ini_set('error_reporting', E_ALL);

if (defined('HHVM_VERSION')) {
    ini_set('memory_limit', '640M');
} else {
    ini_set('memory_limit', '384M');
}

$loader = require __DIR__ . '/../vendor/autoload.php';

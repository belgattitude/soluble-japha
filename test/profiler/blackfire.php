<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

require_once __DIR__.'/../../vendor/autoload.php';

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

try {
    $ba = new BridgeAdapter([
        'driver' => 'Pjb62',
        'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge'
        //'servlet_address' => 'localhost:8080/JavaBridgeSpringboot/servlet.phpjavabridge'
    ]);
} catch (\Exception $e) {
    die('Error connecting: '.$e->getMessage());
}

$iterations = 10;

for ($i = 0; $i < $iterations; ++$i) {
    $ba->java('java.lang.String', 'One');
}

for ($i = 0; $i < $iterations; ++$i) {
    $ba->java('java.math.BigInteger', $i);
}

$jString = $ba->java('java.lang.String', 'Hello world');
for ($i = 0; $i < $iterations; ++$i) {
    $len = $jString->length();
}

$jString = $ba->java('java.lang.String', 'Hello world');
for ($i = 0; $i < $iterations; ++$i) {
    $jString->concat('hello');
}

$arr = ['strKey1' => 'val1',
        'strKey2' => 'val2',
        'strKey3' => 'val3',
        'intKey1' => 1000,
        'boolKey' => true,
        'arrKey' => ['str_val_1', 'str_val_2'],
];

for ($i = 0; $i < $iterations; ++$i) {
    $ba->java('java.util.HashMap', $arr);
}

$hashMap = $ba->java('java.util.HashMap', $arr);
for ($i = 0; $i < $iterations; ++$i) {
    $phpArray = $hashMap->get('arrKey');
    // $arr is a 'io.soluble.pjb.bridge.PhpArray'
    // -> (string) $phpArray[0]);
    // Retrieve the php array version
    // -> var_dump($ba->getDriver()->values($phpArray));
}

echo 'Profiled, see the blackfire.io profile';

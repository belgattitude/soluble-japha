<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

require_once __DIR__.'/../../vendor/autoload.php';

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

ini_set('display_errors', 'true');

$bm = new Benchmark();

$start_total_time = $bm->getTimeMs();

echo '<pre>'.PHP_EOL;

// BENCHING CONNECTION
$start_connection_time = $bm->getTimeMs();
try {
    $ba = new BridgeAdapter([
        'driver' => 'Pjb62',
        //'servlet_address' => 'localhost:8090/servlet.phpjavabridge',
        //'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge',
        'servlet_address' => 'localhost:8080/JasperReports/servlet.phpjavabridge',
        //'servlet_address' => 'localhost:8080/JavaBridgeSpringboot/servlet.phpjavabridge',
        'force_simple_xml_parser' => false,
        'java_prefer_values' => true // the default and recommended way (possible to put at false for tests)
    ]);
    $init = $ba->java('java.lang.String');
} catch (\Exception $e) {
    die(sprintf(
        'Error connecting: %s (%s)',
        $e->getMessage(),
        get_class($e)
    ));
}
$end_connection_time = $bm->getTimeMs();
$connection_time = $bm->getFormattedTimeMs($start_connection_time, $end_connection_time);
// END OF BENCHING CONNECTION

// Test with fileEncoding ASCII
//$ba->getDriver()->setFileEncoding('ASCII');

// BENCHMARK SUITE
/*
$s = microtime(true);
$it = 1;
for ($i=0; $i < $it; $i++) {
    $g = $ba->java('java.lang.String', 'One' . $it);
    $str = $g->__toString();
    if ($str !==  'One' . $it) throw new \Exception('c');
}
echo number_format((microtime(true)-$s) * 1000 / $it, 2);die();
*/

$bm->time(
    'New java(`java.lang.String`, "One")',
    function ($iterations) use ($ba) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.lang.String', 'One');
        }
    }
);

$bm->time(
    'New java(`java.math.BigInteger`, 1)',
    function ($iterations) use ($ba) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.math.BigInteger', $i);
        }
    }
);

$bm->time(
    'javaClass(`java.sql.DriverManager`)',
    function ($iterations) use ($ba) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->javaClass('java.sql.DriverManager');
        }
    }
);

$formatStyle = $ba->javaClass('java.time.format.FormatStyle');

$bm->time(
    'Enums on javaClass',
    function ($iterations) use ($ba, $formatStyle) {
        for ($i = 0; $i < $iterations; ++$i) {
            $style = $formatStyle->LONG;
        }
    }
);

$jString = $ba->java('java.lang.String', 'Hello world');
$bm->time(
    'Method call `java.lang.String->length()`',
    function ($iterations) use ($ba, $jString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $len = $jString->length();
        }
    }
);

$jString = $ba->java('java.lang.String', 'Hello world');
$bm->time(
    'Method call `String->concat("hello")`',
    function ($iterations) use ($ba, $jString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $jString->concat('hello');
        }
    }
);

$jString = $ba->java('java.lang.String', 'Hello world');
$bm->time(
    "\$a = `...String->concat('hello')` . ' world'",
    function ($iterations) use ($ba, $jString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $a = $jString->concat('hello').' world';
        }
    }
);

$arr = [
        'arrKey' => ['str_val_1' => 'test', 'str_val_2' => 'test'],
];

$bm->time(
    'New java(`java.util.HashMap`, $arr)',
    function ($iterations) use ($ba, $arr) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.util.HashMap', $arr);
        }
    }
);

$hashMap = $ba->java('java.util.HashMap', $arr);
$bm->time(
    'Method call `HashMap->get(\'arrKey\')`',
    function ($iterations) use ($ba, $hashMap) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpArray = $hashMap->get('arrKey');
            // $arr is a 'io.soluble.pjb.bridge.PhpArray'
            // -> (string) $phpArray[0]);
            // Retrieve the php array version
            // -> var_dump($ba->getDriver()->values($phpArray));
        }
    }
);

$hashMap = $ba->java('java.util.HashMap', $arr);
$bm->time(
    'Call `(string) HashMap->get(\'arrKey\')[0]`',
    function ($iterations) use ($ba, $hashMap) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpArray = $hashMap->get('arrKey');
            $str = (string) $phpArray[0];
        }
    }
);

$hashMap = $ba->java('java.util.HashMap', $arr);
$bm->time(
    'Iterate HashMap->get(\'arrKey\')[0]`',
    function ($iterations) use ($ba, $hashMap) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpArray = $hashMap->get('arrKey');
            foreach ($phpArray as $value) {
                $str = (string) $value;
            }
        }
    }
);

$hashMap = $ba->java('java.util.HashMap', $arr);
$bm->time(
    'GetValues on `HashMap`',
    function ($iterations) use ($ba, $hashMap) {
        for ($i = 0; $i < $iterations; ++$i) {
            $vals = $ba->values($hashMap);
        }
    }
);

$bigArray = array_fill(0, 100, true);
$bm->time(
    'New `java(HashMap(array_fill(0, 100, true)))`',
    function ($iterations) use ($ba, $bigArray) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.util.HashMap', $bigArray);
        }
    }
);

$bm->time(
    'Pure PHP: call PHP strlen() method',
    function ($iterations) {
        for ($i = 0; $i < $iterations; ++$i) {
            strlen('Hello World');
        }
    }
);

$phpString = 'Hello world';
$bm->time(
    'Pure PHP: concat \'$string . "hello"\' ',
    function ($iterations) use (&$phpString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpString = $phpString.'Hello World';
        }
    }
);

$end_total_time = $bm->getTimeMs();
$total_time = $bm->getFormattedTimeMs($start_total_time, $end_total_time);

echo PHP_EOL;
echo '- Connection time: '.$connection_time.PHP_EOL;
echo '- Total time     : '.$total_time.PHP_EOL;
echo PHP_EOL;

class Benchmark
{
    /**
     * @var bool
     */
    public $tableHeaderPrinted = false;

    /**
     * @var array
     */
    public $iterations = [1, 100, 1000, 10000];

    public function __construct()
    {
    }

    /**
     * @param string   $name
     * @param callable $fn
     */
    public function time($name, callable $fn)
    {
        if (!$this->tableHeaderPrinted) {
            echo '| Benchmark name | '.implode('|', array_map(function ($iter) {
                return " x$iter ";
            }, $this->iterations)).'| Average | Memory |'.PHP_EOL;
            echo '|----| '.implode('|', array_map(function ($iter) {
                return '----:';
            }, $this->iterations)).'|-------:|----:| '.PHP_EOL;
            $this->tableHeaderPrinted = true;
        }

        $times = [];

        $start_memory = memory_get_usage(false);

        foreach ($this->iterations as $iteration) {
            $start_time = microtime(true);
            $fn($iteration);
            $total_time = microtime(true) - $start_time;
            $times[$iteration] = $total_time;
        }

        $memory = memory_get_usage(false) - $start_memory;

        $avg = array_sum($times) / array_sum(array_keys($times));

        /*
        $ttime = array_sum($times);
        echo number_format($ttime * 1000, 2);
        */
        echo  "| $name | ".implode('| ', array_map(function ($time) {
            return number_format($time * 1000, 2).'ms';
        }, $times)).'| '.
            number_format($avg * 1000, 2).'ms| '.
            round($memory / 1024, 2).'Kb'.'|'.PHP_EOL;
    }

    /**
     * Return formatted time .
     *
     * @param int $start_time
     * @param int $end_time
     */
    public function getFormattedTimeMs($start_time, $end_time)
    {
        $time = $end_time - $start_time;

        return number_format($time, 0, '.', '').' ms';
    }

    /**
     * Get ms time (only 64bits platform).
     *
     * @return int
     */
    public function getTimeMs()
    {
        $mt = explode(' ', microtime());

        return ((int) $mt[1]) * 1000 + ((int) round($mt[0] * 1000));
    }
}

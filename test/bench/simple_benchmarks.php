<?php
/**
 * PHPJavaBridge - poor man dirty benchmarks, hopefully not copyrighted :).
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$bm = new Benchmark();

$start_total_time = microtime(true);

echo '<pre>' . PHP_EOL;

// BENCHING CONNECTION
$start_connection_time = microtime(true);
try {
    $ba = new BridgeAdapter([
        'driver' => 'Pjb62',
        'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge'
        //'servlet_address' => 'localhost:8080/JavaBridgeSpringboot/servlet.phpjavabridge'
    ]);
    $init = $ba->java('java.lang.String');
} catch (\Exception $e) {
    die('Error connecting: ' . $e->getMessage());
}
$end_connection_time = microtime(true);
$connection_time = $bm->getFormattedTime($start_connection_time, $end_connection_time);
// END OF BENCHING CONNECTION

// BENCHMARK SUITE

$bm->time('New java(`java.lang.String`, "One")',
    function ($iterations) use ($ba) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.lang.String', 'One');
        }
    });

$bm->time('New java(`java.math.BigInteger`, 1)',
    function ($iterations) use ($ba) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.math.BigInteger', $i);
        }
    });

$jString = $ba->java('java.lang.String', 'Hello world');
$bm->time('Method call `java.lang.String->length()`',
    function ($iterations) use ($ba, $jString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $len = $jString->length();
        }
    });

$jString = $ba->java('java.lang.String', 'Hello world');
$bm->time('Method call `String->concat("hello")`',
    function ($iterations) use ($ba, $jString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $jString->concat('hello');
        }
    });

$jString = $ba->java('java.lang.String', 'Hello world');
$bm->time("\$a = `...String->concat('hello')` . ' world'",
    function ($iterations) use ($ba, $jString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $a = $jString->concat('hello') . ' world';
        }
    });

$arr = ['strKey1' => 'val1',
        'strKey2' => 'val2',
        'strKey3' => 'val3',
        'intKey1' => 1000,
        'boolKey' => true,
        'arrKey' => ['str_val_1', 'str_val_2'],
];

$bm->time('New java(`java.util.HashMap`, $arr)',
    function ($iterations) use ($ba, $arr) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.util.HashMap', $arr);
        }
    });

$hashMap = $ba->java('java.util.HashMap', $arr);
$bm->time('Method call `HashMap->get(\'arrKey\')`',
    function ($iterations) use ($ba, $hashMap) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpArray = $hashMap->get('arrKey');
            // $arr is a 'io.soluble.pjb.bridge.PhpArray'
            // -> (string) $phpArray[0]);
            // Retrieve the php array version
            // -> var_dump($ba->getDriver()->values($phpArray));
        }
    });

$hashMap = $ba->java('java.util.HashMap', $arr);
$bm->time('Call `(string) HashMap->get(\'arrKey\')[0]`',
    function ($iterations) use ($ba, $hashMap) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpArray = $hashMap->get('arrKey');
            $str = (string) $phpArray[0];
        }
    });

$bigArray = array_fill(0, 100, true);
$bm->time('New `java(HashMap(array_fill(0, 100, true)))`',
    function ($iterations) use ($ba, $bigArray) {
        for ($i = 0; $i < $iterations; ++$i) {
            $ba->java('java.util.HashMap', $bigArray);
        }
    });

$bm->time('Pure PHP: call PHP strlen() method',
    function ($iterations) {
        for ($i = 0; $i < $iterations; ++$i) {
            strlen('Hello World');
        }
    });

$phpString = 'Hello world';
$bm->time('Pure PHP: concat \'$string . "hello"\' ',
    function ($iterations) use (&$phpString) {
        for ($i = 0; $i < $iterations; ++$i) {
            $phpString = $phpString . 'Hello World';
        }
    });

$end_total_time = microtime(true);
$total_time = $bm->getFormattedTime($start_total_time, $end_total_time);

echo PHP_EOL;
echo '- Connection time: ' . $connection_time . 'ms' . PHP_EOL;
echo '- Total time     : ' . $total_time . 'ms' . PHP_EOL;
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
            echo '| Benchmark name | ' . implode('|', array_map(function ($iter) {
                return " x$iter ";
            }, $this->iterations)) . '| Average | Memory |' . PHP_EOL;
            echo '|----| ' . implode('|', array_map(function ($iter) {
                return '----:';
            }, $this->iterations)) . '|-------:|----:| ' . PHP_EOL;
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

        $avg = number_format((array_sum($times) / array_sum(array_keys($times)) * 1000), 4) . 'ms';
        echo  "| $name | " . implode('| ', array_map(function ($time) {
            return number_format($time * 1000, 2) . 'ms';
        }, $times)) . '|' . $avg . '|' .
            round($memory / 1024, 2) . 'Kb' . '|' . PHP_EOL;
    }

    /**
     * Return formatted time in ms.
     *
     * @param float $start_time
     * @param float $end_time
     */
    public function getFormattedTime($start_time, $end_time)
    {
        $time = $end_time - $start_time;

        return number_format($time * 1000, 2);
    }
}

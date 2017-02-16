<?php
/**
 * PHPJavaBridge - poor man dirty benchmarks, hopefully not copyrighted :).
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$bm = new Benchmark();

echo '<pre>' . PHP_EOL;

// BENCHING CONNECTION
$bench = $bm->bench;
$bm->printHeader();
// Connection test
$bm->bench->start();
try {
    $ba = new BridgeAdapter([
        'driver' => 'Pjb62',
        'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge'
        //'servlet_address' => 'localhost:8080/JavaBridgeSpringboot/servlet.phpjavabridge'
    ]);
    $bigInt = $ba->java('java.math.BigInteger', 1);
} catch (\Exception $e) {
    die('Error connecting: ' . $e->getMessage());
}
// Retrieve an object

$bm->bench->end();
$bm->printResult('Connection', $bench);
// END OF BENCHING CONNECTION

// BENCHMARK SUITE

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
$bm->time('Method call `java.lang.String->concat("hello")`',
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

class Benchmark
{
    /**
     * @var Ubench
     */
    public $bench;

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
        $this->bench = new Ubench();
    }

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

    public function printResult($name, Ubench $bench)
    {
        echo "| $name |", implode('|', [
                $bench->getTime(false, '%d%s'),
                $bench->getMemoryUsage(),
                $bench->getMemoryPeak(),
            ]) . '|' . PHP_EOL;
    }

    public function printHeader()
    {
        echo '| Test | Time | Memory usage | Memory peak |' . PHP_EOL;
        echo '| ---- | ---- | ------------ | ----------- |' . PHP_EOL;
    }
}

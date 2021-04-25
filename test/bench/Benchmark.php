<?php

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

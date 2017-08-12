<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src')
;

return new Sami($iterator, array(
    'title'                => 'soluble japha',
    'build_dir'            => __DIR__.'/build/doc/api',
    'cache_dir'            => __DIR__.'/cache',
));
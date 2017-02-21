# Legacy notes

## Introduction

Historically the [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) client implementation didn't
support namespaces. If you have existing code relying on previous implementations and don't want to refactor, 
you can install a [compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat).
 
## Installation

Simply add the `soluble/japha` compatibility layer to your [composer](http://getcomposer.org/) dependencies :

```console
$ composer require "soluble/japha-pjb62-compat"
```

## Documentation

See the [official repo](https://github.com/belgattitude/soluble-japha-pjb62-compat).


## Differences with the legacy Java.inc client.

The original `Java.inc` client has been completely refactored to fit current trends modern in PHP practices (2016).

- [x] New API (not backward compatible)

    All global functions have been removed (`java_*`) in favour of a more object oriented approach. 
    By doing so, the new API breaks compatibility with existing code if you have code written against 
    the `Java.inc` original client), but offers the possibility to rely on different driver implementations 
    without breaking your code.

- [x] PHP version and ecosystem

    - [x] PHP7, HHVM ready (PHP 5.5+ supported).
    - [x] Installable with composer
    - [x] Compliant with latests standards: PSR-2, PSR-3, PSR-4

- [x] Enhancements    
    
    - [x] Namespaces introduced everywhere. 
    - [x] Removed global namespace pollution (java_* functions)
    - [x] Removed global variables, functions and unscoped statics.
    - [x] No more get_last_exception... (All exceptions are thrown with reference to context)
    - [x] Autoloading performance (no more one big class, psr4 autoloader is used, less memory)
    - [x] Removed long time deprecated features in Java.inc
    - [x] By design, no more allow_url_fopen needed.
    
- [x] Fixes
    
    - [x] All notices, warnings have been removed
    - [x] Some minor bugs found thanks to the unit tests suite

- [x] Quality
   
    - [x] All code is tested (phpunit, travis), analyzed (scrunitizer) to prevent regressions.



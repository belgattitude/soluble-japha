# Legacy notes

## Introduction

The current soluble-japha `Pjb62Driver` driver is based on the original 
[PHP/Java bridge `Java.inc`](http://php-java-bridge.sourceforge.net/pjb/) implementation
and have been heavily refactored. See the differences below:

!!! tip 
    If you have existing code relying on previous implementations and don't want to refactor, 
    see the [compatibility layer](#compatibility-layer) section.


### Refactorings

The original `Java.inc` client has been completely refactored to fit more modern trends in PHP practices (2016).

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

 
 
## Compatibility layer 

Backward compatibility with the `Java.inc` client can be done through the
[soluble-japha-pjb62-compat](https://github.com/belgattitude/soluble-japha-pjb62-compat) lib:
 
### Installation

Simply add the `soluble/japha` compatibility layer to your [composer](http://getcomposer.org/) dependencies :

```console
$ composer require "soluble/japha-pjb62-compat"
```

and check the [official repo] for doc and current status.



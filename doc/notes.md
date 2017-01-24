### Support

Please fill any issues on the [offical tracker](https://github.com/belgattitude/soluble-japha/issues). 
If you like to contribute, see the <a href="https://github.com/belgattitude/soluble-japha/blob/master/CONTRIBUTING.md">contribution guidelines</a>

### Status

Client API can be considered stable. 

Although semantic versioning will only be respected from v1.0.0 release, only minor modifications to the API will be considered at that point. 

[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)

At time of writing this document (Jul 16), the version 0.11.6 passes 57 unit tests and 582 assertions 
for a coverage of 58%. *(The low degree of coverage is mainly due to a lot of obsolete code in 
the pjb driver code that is still to be removed once reaching v1).*

### Changelog

Versions and changelog are documented on the <a href="https://github.com/belgattitude/soluble-japha/blob/master/CHANGELOG.md">changelog page</a>

### Differences with the legacy Java.inc client.

The original `Java.inc` client has been completely refactored to fit current trends modern in PHP practices (2016).

- New API (not backward compatible)

    All global functions have been removed (`java_*`) in favour of a more object oriented approach. 
    By doing so, the new API breaks compatibility with existing code (see the 
    [legacy compatibility guide](./pjb62_compatibility.md) if you have code written against 
    the `Java.inc` original client), but offers the possibility to rely on different driver implementations 
    without breaking your code.

- PHP version and ecosystem

    - PHP7, HHVM ready (PHP 5.5+ supported).
    - Installable with composer
    - Compliant with latests standards: PSR-2, PSR-3, PSR-4

- Enhancements    
    
    - Namespaces introduced everywhere. 
    - Removed global namespace pollution (java_* functions)
    - Removed global variables, functions and unscoped statics.
    - No more get_last_exception... (All exceptions are thrown with reference to context)
    - Autoloading performance (no more one big class, psr4 autoloader is used, less memory)
    - Removed long time deprecated features in Java.inc
    - By design, no more allow_url_fopen needed.
    
- Fixes
    
    - All notices, warnings have been removed
    - Some minor bugs found thanks to the unit tests suite

- Testing
   
    - All code is tested (phpunit, travis), analyzed (scrunitizer)


### Credits

* This code is principally developed and maintained by [Sébastien Vanvelthem](https://github.com/belgattitude).
* Special thanks to [all of these awesome contributors](https://github.com/belgattitude/soluble-japha/network/members)
* This project is based on the Java.inc work made by the [PHPJavaBridge developers](http://php-java-bridge.sourceforge.net/pjb/contact.php#code_contrib). 

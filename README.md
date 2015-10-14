# Soluble\Japha

[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.png?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![Dependency Status](https://www.versioneye.com/user/projects/52cc2674ec137549700001f3/badge.png)](https://www.versioneye.com/user/projects/52cc2674ec137549700001f3)
[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)

## Introduction

* This is an alpha project, documentation and working examples coming 

PHP javabridge client 

## Requirements

This library supports PHP 5.3+, PHP 7 and HHVM.


## Features

- Use Java in PHP
- Unit tested

How it differs from original phpjavabridge client (Java.inc)
- Refactored code to adhere to latests standards (PSR-2, PSR-4)
- Namespaced code
- Removed notices and warnings.
- Does not require allow_url_open
- Refactored exception model in order to produce 
- HHVM, PHP 7 support


## Installation

Soluble/Japha can be installed via composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).


The recommended way to install Soluble\Japha is through `Composer <https://getcomposer.org/>`_.
Just add soluble/japha in your composer.json file as described below

```sh
php composer.phar require soluble/japha:0.*
```

Server side servlet. see PHPJavaBridge on Tomcat.


## Examples

```php

use Soluble\Japha\Bridge\PhpJavaBridge as Pjb;
use Soluble\Japha\Bridge\Exception;

$driverManager = Pjb::getJavaClass('java.sql.DriverManager');

$driverClass = 'com.mysql.jdbc.Driver';
$dsn = "jdbc:mysql://localhost/$db?user=login&password=pwd";

try {

    $class = Pjb::getJavaClass("java.lang.Class");
    $class->forName($driverClass);
    
    $conn = $driverManager->getConnection($dsn);

} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
}

```

## Coding standards

Please follow the following guides and code standards:

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)


[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)



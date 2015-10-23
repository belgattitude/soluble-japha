# Soluble\Japha

[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.png?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![HHVM Status](http://hhvm.h4cc.de/badge/soluble/japha.png?style=flat)](http://hhvm.h4cc.de/package/soluble/japha)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)

** This is an alpha project, documentation and working examples coming **

## Introduction

An enhanced compatible version of the PHPJavaBridge php implementation.

## Features

- Use Java from PHP

## Requirements

- PHP engine 5.3+, 7.0 or HHVM >= 3.2.
- Java servlet engine (Tomcat, Jetty,...) 

## Installation

### Installation in your PHP project

soluble-japha works best via [composer](http://getcomposer.org/).

```sh
php composer require soluble/japha:0.*
```

### Java servlet engine

See Java server installation in the doc folder for more information. 

## Examples

### Connection example

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62',
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
]);
```

### Basic Java usage

```php
<?php

$ba = new BridgeAdapter([
    'driver' => 'Pjb62',
    'servlet_address' => 'http://localhost:8083/path/servlet.phpjavabridge'
]);

$string = $ba->java('java.lang.String', "保éà");
$hash   = $ba->java('java.util.HashMap', array('my_key' => 'my_value'));
$hash->put('new_key', $string);
$hash->put('hello', "world");
echo $hash->get('new_key'); // prints "保éà"
echo $hash->get('new_key')->length(); // prints 3
echo $hash->get('hello'); // prints "world"

```

### Using "final" classes

```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

$system = $ba->javaClass('java.lang.System');

$vm_name = $system->getProperties()->get('java.vm_name);
```

### Database connection example

```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

$driverClass = 'com.mysql.jdbc.Driver';
$dsn = "jdbc:mysql://localhost/my_database?user=login&password=pwd";

try {

    $class = $ba->javaClass('java.lang.Class');
    $class->forName($driverClass);
    
    $conn = $driverManager->getConnection($dsn);

} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
}

```

### Compatibility layer

If you have legacy code using the original PHPJavaBridge implementation, 
just enable compatibility layer through the 'load_pjb_compatibility'.

Also note that you'll have to remove any older constants like 'JAVA_HOSTS', 'JAVA_SERVLET'...

See also Soluble\Japha\Bridge\Driver\Pjb62\Compat\pjb_functions.php. 

```php
<?php

// Boostrap the adapter globally

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62',
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
    'load_pjb_compatibility' => true
]);

// Once done, the legacy code should work

$bigint = new Java("java.math.BigInteger", 1);
$system = java_class('java.lang.System);

java_instanceof($bigint, 'java.math.BigInteger'); // -> true
java_inspect($bigint); 
java_values($bigint);
java_invoke();

```

## Original PHPJavaBridge (Java.inc) differences

- New API
  - A fresh new API to allow future drivers and enhancements.
  - A more intuitive and verbose exception handling.
  - For legacy code a compatibility layer can be loaded.

- Performance
  - Reduced memory usage and faster execution.
  - Opcache friendly (no allow_url_open)

- PHPJavaBridge refactorings (Java.inc)
  - PHP 5.3+ namespaces, PSR-4 autoloading and PSR-2 coding style.
  - Removed global variables, functions and unscoped statics.
  - Removed most notices and warnings.
  - No allow_url_open possible (security)
  - Removed deprecated code.

## Credits

Thanks to the fantastic PHPJavaBridge project leaders and contributors who made it possible. 
See their official homepage on http://php-java-bridge.sourceforge.net/pjb/index.php.

## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)


[![Dependency Status](https://www.versioneye.com/user/projects/5624cd4636d0ab0019000b2e/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5624cd4636d0ab0019000b2e)

[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)



[![PHP Version](http://img.shields.io/badge/php-5.5+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)
[![HHVM Status](http://hhvm.h4cc.de/badge/soluble/japha.svg)](http://hhvm.h4cc.de/package/soluble/japha)

## Introduction

**soluble-japha** provides a client to write Java code transparently in regular PHP. 

*Originally based on the robust and mature [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) project, 
soluble-japha leverage the initial client implementation by offering a refactored and modern 
approach while preserving compatibility with the server.* 
 
## Use cases 

It differs from the idea of api, microservices... where communication requires a contract 
between the client and server in order to exchange information. With soluble-japha, 
you can transparently use Java objects, methods... which will be proxied to the JVM.  
  
With that in mind you can use it to broaden the PHP possibilities to the Java ecosystem and its bunch 
of compelling libraries *(i.e. Jasper Reports, Apache POI, iText, PDFBox, DeepLearning...)* or simply 
establish the bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun.

*If you write a (wrapper) library with soluble-japha, you might be interested in the 
[pjbserver-tools standalone server](https://github.com/belgattitude/pjbserver-tools) that helps to
setup unit testing on a CI server (Travis...)*

## Features

- Write Java from PHP (with a little extra php-style ;)  
- Compatible with [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) server implementation.
- Efficient, no startup effort, native communication with the JVM (([JSR-223](https://en.wikipedia.org/wiki/Scripting_for_the_Java_Platform) spec).
- Java objects, methods calls... are proxied to the server through a fast XML-based network protocol.
- API extensible to multiple server implementations (Adapter pattern). 
- MIT opensource licensed.
- *For support with older `Java.inc` client, see the [legacy compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat).*

## Requirements

- PHP 5.5+, 7.0+, 7.1+ or HHVM >= 3.2.
- Installed [JRE or JDK 7+](./doc/server/install_java.md).
- A PHP-Java bridge server [installed](./doc/quick_install.md).

## Documentation

 - [Manual](http://docs.soluble.io/soluble-japha/manual/) in progress. 
 - [API documentation](http://docs.soluble.io/soluble-japha/api/) available.

## Installation

1. PHP installation *(client)*

   `soluble-japha` works best via [composer](http://getcomposer.org/).

   ```console
   $ composer require soluble/japha
   ```

   Most modern frameworks will include Composer out of the box, but ensure the following file is included:

   ```php
   <?php
   // include the composer autoloader
   require 'vendor/autoload.php';
   ```

2. PHP-Java-bridge standalone server

   PHP-Java communication requires a PHP-Java-bridge server running on your local machine or network *(on a non-plublic port)*.
   
   For a quick install, clone the [pjbserver-tools standalone server](https://github.com/belgattitude/pjbserver-tools) repository in a custom directory an run [composer](http://getcomposer.org) update command.
   
   ```console
   $ mkdir -p /my/path/pjbserver-tools
   $ cd /my/path/pjbserver-tools
   $ git clone https://github.com/belgattitude/pjbserver-tools.git .
   $ composer update   
   $ ./bin/pjbserver-tools pjbserver:start -vvv ./config/pjbserver.config.php.dist
   ```

   The server will start on default port ***8089***. If you like to change it, create a local copy of `./config/pjbserver.config.php.dist`
   and refer it in the above command.
   
   Use the commands `pjbserver:stop`, `pjbserver:restart`, `pjbserver:status` to control or query the server status.

   Get information about the standalone server on the [pjbserver-tools repo](https://github.com/belgattitude/pjbserver-tools). 
   
   For production systems a Tomcat installation is encouraged, see the [server installation guide](./doc/quick_install.md) to get an overview of possible strategies.

          
## Examples

Here's some quick examples to get a feeling, don't forget to check out the [official documentation site](http://docs.soluble.io/soluble-japha/manual/).

### 1. Connection example

Configure your bridge adapter with the correct driver (currently only Pjb62 is supported) and the PHP-Java-bridge server address.

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
]);
```
 

### 2. Hello word

```php
<?php

// $ba = new BridgeAdapter(...); 

$myJavaString = $ba->java('java.lang.String', "Hello");

// concat method on jave string object
// see http://docs.oracle.com/javase/7/docs/api/java/lang/String.html

$myJavaString->concat(" world");  

echo $myJavaString;  

// -> Outputs Hello world

```

### 3. Get your JVM info

```php
<?php

// $ba = new BridgeAdapter(...); 

$system = $ba->javaClass('java.lang.System');
echo  $system->getProperties()->get('java.vm_name);

```


### 4. JDBC example

Ensure your servlet installation can locate the JDBC driver and try :

```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

$driverClass = 'com.mysql.jdbc.Driver';
$dsn = "jdbc:mysql://localhost/my_database?user=login&password=pwd";

try {

    $driverManager = $ba->javaClass('java.sql.DriverManager');

    $class = $ba->javaClass('java.lang.Class');
    $class->forName($driverClass);
    
    $conn = $driverManager->getConnection($dsn);

} catch (Exception\ClassNotFoundException $e) {
    // Probably the jdbc driver is not registered
    // on the JVM side. Check that the mysql-connector.jar
    // is installed
    echo $e->getMessage();
    echo $e->getStackTrace();
} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
}
try {
    $stmt = $conn->createStatement();
    $rs = $stmt->executeQuery('select * from product');
    while ($rs->next()) {
        $title = $rs->getString("title");
        echo $title;            
    }        
    if (!$ba->isNull($rs)) {
        $rs->close();
    }
    if (!$ba->isNull($stmt)) {
        $stmt->close();
    }
    $conn->close();
} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    // Because it's a JavaException
    // you can use the java stack trace
    echo $e->getStackTrace();
} catch (\Exception $e) {
    echo $e->getMessage();
}

```

For more examples and recipes, have a look at the [official documentation site](http://docs.soluble.io/soluble-japha/manual/). 


### Original PHPJavaBridge (Java.inc) differences

The original `Java.inc` client has been completely refactored to fit current trends in PHP practices (2016).

- New API (not backward compatible)

    All global functions have been removed (`java_*`) in favour of a more object oriented approach. 
    By doing so, the new API breaks compatibility with existing code (see the 
    [legacy compatibility guide](./doc/pjb62_compatibility.md) if you have code written against 
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
 

## Compatibility layer

Take a look to [legacy compatibility guide](./doc/pjb62_compatibility.md) for more information.

## Future ideas

- Original code improvements
  - Achieve at least 80% of unit testing for legacy code.
  - Refactor as much as possible and remove dead code.

- Supporting more drivers or techs
  - [Zend Java bridge](http://files.zend.com/help/Zend-Platform/about.htm) driver compatibility.
  - [GRPC](http://www.grpc.io/) 
  - Support the [MethodHandles](http://docs.oracle.com/javase/7/docs/api/java/lang/invoke/MethodHandles.html) and [InvokeDynamic](http://docs.oracle.com/javase/7/docs/api/java/lang/invoke/package-summary.html) APIs described in [JSR-292](https://jcp.org/en/jsr/detail?id=292).

- Improve proxy
  - see [ProxyManager](https://github.com/Ocramius/ProxyManager)

- Explore new possibilities 
  - Create a JSR-223 php extension in Go, like this [experiment](https://github.com/do-aki/gophp_sample)


### Credits

This project wouldn't be possible without the PHPJavaBridge project leaders and contributors. 
See their official homepage on http://php-java-bridge.sourceforge.net/pjb/index.php.

## Coding standards and interop

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)







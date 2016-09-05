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

Originally based on the robust and mature [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) project, 
**soluble-japha** leverage the initial client implementation by offering a more modern approach 
*(namespaces, psr standards, php7 ready...)* and support composer for easy dependency requirement 
in a PHP project or your next creative Java-based wrapper library. 
 
## Use cases 

It differs from the idea of api, microservices... where communication requires a contract 
between the client and server in order to exchange information. With soluble-japha, 
you can transparently use Java objects, methods... which will be proxied to the JVM.  
  
With that in mind you can use it to broaden the PHP possibilities to the Java ecosystem and its bunch 
of compelling libraries *(i.e. Jasper Reports, Apache POI, iText, PDFBox, DeepLearning...)* or simply 
establish the bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun.

## Features

- Provides the core to access the Java world from PHP. 
- Client API for [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) server implementation of the [JSR-223](https://en.wikipedia.org/wiki/Scripting_for_the_Java_Platform) spec.
- Use a fast XML-based network protocol behind the scenes. *(no JVM startup efforts)*
- For support with original `Java.inc` client, see the [legacy compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat).
- MIT opensource license.

## Requirements

- PHP 5.5+, 7.0+ or HHVM >= 3.2.
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
   
   See the [server installation guide](./doc/quick_install.md) to get an overview of possible strategies.
   
   
   Alternatively you can quickly clone the [pjbserver-tools standalone server](https://github.com/belgattitude/pjbserver-tools) repository in a custom directory an run [composer](http://getcomposer.org) update command.
   
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

   For more information about the standalone server, have a look to the [pjbserver-tools repo](https://github.com/belgattitude/pjbserver-tools). 
       
          
## Examples


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
 

### 2. JDBC example

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
    //...
}

```

For more examples and recipes, have a look at the [official documentation site](http://docs.soluble.io/soluble-japha/manual/). 

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


## Credits

Thanks to the fantastic PHPJavaBridge project leaders and contributors who made it possible. 
See their official homepage on http://php-java-bridge.sourceforge.net/pjb/index.php.

## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)







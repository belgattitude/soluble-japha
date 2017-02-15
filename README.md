[![PHP Version](http://img.shields.io/badge/php-5.5+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)
[![HHVM Status](https://php-eye.com/badge/soluble/japha/hhvm.svg)](https://php-eye.com/package/soluble/japha)


In short **soluble-japha** allows to write Java code in PHP interacting with the JVM and its huge ecosystem. 
As a meaningless example, see the code below:

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
]);

// An utf8 string
$string = $ba->java('java.lang.String', "Hello world!");
$hash   = $ba->java('java.util.HashMap', ['key1' => $string, 'key2' => 'hello']);
echo $hash->get('key1'); // prints "Hello world"
echo $hash->get('key2')->length(); // prints 4

// Some maths
$bigint = $ba->java("java.math.BigInteger", 1);
echo $bigint->intValue() + 10; // prints 11

```

Behind the scenes, it communicates with a [PHP/Java bridge](https://github.com/belgattitude/php-java-bridge) server which exposes the JVM 
through a specific network protocol. This way all libraries registered on the JVM can be used from PHP, almost just like you could write code
in Java. The Java code is still executed on the JVM, results being sent back to PHP transparently. 
      
## Use cases 

Whenever you need to communicate transparently with the JVM, more specifically with its rich set 
of libraries *(i.e. Jasper Reports, Apache POI, iText, PDFBox, Machine Learning...)* or simply 
establish a bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun :) 
  
## Features

- Write Java from PHP (with a little extra php-style ;)  
- Compatible with [PHP/Java bridge](https://github.com/belgattitude/php-java-bridge) server implementation.
- Efficient, no startup effort, native communication with the JVM ([JSR-223](https://en.wikipedia.org/wiki/Scripting_for_the_Java_Platform) spec).
- Java objects, methods calls... are proxied and executed on the JVM. 

> For user of previous versions, **soluble-japha** client replaces the original/legacy [PHPJavaBridge](http://php-java-bridge.sourceforge.net/pjb/) 
> `Java.inc` implementation and has been completely refactored to fit modern practices 
> and PHP7. 
> See the [differences here](./doc/notes.md) and the [legacy compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat) if needed.

## Requirements

- PHP 5.6, 7.0+, 7.1+ or HHVM >= 3.9 *(for PHP5.5 use the "^0.13.0" releases)*.
- Installed [JRE or JDK 7/8+](./doc/server/install_java.md).
- A PHP-Java bridge server [installed](./doc/quick_install.md).

## Documentation

 - [Manual](http://docs.soluble.io/soluble-japha/manual/) in progress. 
 - [API documentation](http://docs.soluble.io/soluble-japha/api/) available.

## Installation

1. Installation in your PHP project **(client)**
 
   ```console
   $ composer require soluble/japha
   ```

2. PHPJavaBridge **(server)**
   
   The most easy way is to build your own PHPJavaBridge server with the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot) 
   and customize it to include your required dependencies. As an example:
    
   ```console
   $ git clone https://github.com/belgattitude/pjb-starter-springboot
   $ cd pjb-starter-springboot
   $ # An example build with jasperreports and mysql jdbc connector included
   $ ./gradlew build -I init-scripts/init.jasperreports.gradle -I init-scripts/init.mysql.gradle
   $ # Run the PHPJavaBridge server
   $ java -jar ./build/libs/JavaBridgeStandalone.jar -Dserver_port=8089   
   ``` 
   
   Check the [landing page](http://localhost:8089) for status and use the connection `localhost:8089/servlet.phpjavabridge` in your bridge connection parameters.
   
   See more customizations examples on the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot) 
   project, especially tomcat support (as simple as doing `cp ./build/libs/JavaBridgeTemplate.war /var/lib/tomcat8/webapps/MyJavaBridge.war`).
         
   *Other alternatives exists like the [pjbserver-tools standalone server](https://github.com/belgattitude/pjbserver-tools) installable
   from composer or the barebone installation from the soluble [PHPJavaBridge](https://github.com/belgattitude/php-java-bridge) fork, see 
   the [install_server.md](./doc/install_server.md)*      
               
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

// concat method on java string object
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
echo  $system->getProperties()->get('java.vm_name');

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

### SSL client socket, readers and writers

```php
<?php

// $ba = new BridgeAdapter(...); 

$serverPort = 443;
$host = 'www.google.com';

$socketFactory = $ba->javaClass('javax.net.ssl.SSLSocketFactory')->getDefault();
$socket = $socketFactory->createSocket($host, $serverPort);

$socket->startHandshake();
$bufferedWriter = $ba->java('java.io.BufferedWriter',
            $ba->java('java.io.OutputStreamWriter',
                    $socket->getOutputStream()
            )
        );

$bufferedReader = $ba->java('java.io.BufferedReader',
            $ba->java('java.io.InputStreamReader',
                $socket->getInputStream()
            )
        );

$bufferedWriter->write("GET / HTTP/1.0");
$bufferedWriter->newLine();
$bufferedWriter->newLine(); // end of HTTP request
$bufferedWriter->flush();

$lines = [];
do {
    $line = $bufferedReader->readLine();
    $lines[] = (string) $line;
} while(!$ba->isNull($line));

$content = implode("\n", $lines);
echo $content;

$bufferedWriter->close();
$bufferedReader->close();
$socket->close();

```

For more examples and recipes, have a look at the [official documentation site](http://docs.soluble.io/soluble-japha/manual/). 
 

## Compatibility layer with legacy versions

If you rely on previous implementations of the PHPJavaBridge (the `Java.inc` client), 
have a look to [legacy compatibility guide](./doc/pjb62_compatibility.md) which can help
to gives some tips for migrations.


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

* This code is principally developed and maintained by [Sébastien Vanvelthem](https://github.com/belgattitude).
* Special thanks to [all of these awesome contributors](https://github.com/belgattitude/soluble-japha/network/members)
* This project is based on the Java.inc work made by the [PHPJavaBridge developers](http://php-java-bridge.sourceforge.net/pjb/contact.php#code_contrib). 
  
## Coding standards and interop

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)


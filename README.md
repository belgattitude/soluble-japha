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
of libraries *(i.e. Jasper Reports, POI, iText, PDFBox, Android, Machine Learning...)* or simply 
establish a bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun :) 

*See also the [considerations, performance and best pratices](https://github.com/belgattitude/soluble-japha#considerations) before
implementing a solution based on the bridge.*
  
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
 
## Considerations

Before considering using the bridge you should be aware that its performance is sensitive to 
the number of Java method calls and object instantiations...In other words, if you intend to write 
some code looping multiple thousands of results... better to go off with a different approach like developing 
a (micro-)service on the Java side and consume it with PHP. (*not totally true as you can, for example, convert a
ResultSet into an array on the Java side and retrieve it with one method call...*). See the performances 
and overhead considerations below to get a glimpse.

That said, the bridge shines whenever you need to access *programmatically* a Java library (think
JasperReports, POI, NLP, Jsoup, Android...) with maximum flexibity (level of control: the code) and
benefit from the performance of the JVM (make a 1000 pages PDF with PHP in less than 100ms, anyone ?).
The choice becomes even more clear for libraries with no equivalent in PHP (entreprise or esoteric libs/drivers...). 
   
Note that the server side installation has vastly improved (customization, build and deployment can be scripted in few lines), 
its minimal requirement is to have a recent JVM installed. You can even package a server with your composer lib.     
 
### How it works

The bridge operates by forwarding each Java object instantiations and method calls 
through the connection tunnel (`BridgeAdapter`). 

You can think about it like a database connection on which you execute tiny queries, but
with some differences: 

The protocol used between Java and PHP is based on HTTP and serialized in XML. 
Here's what would be transmitted if you call `$ba->javaClass('myJClass')->aJMethod(2)`:
    
```xml
<C value="myJClass" p="Class"></C>
<I value="0" method="aJMethod" p="Invoke"><Object value="2"/></I>
```    

In addition to this, object state is *automatically* maintained between both Java and PHP runtimes.
The PHP client keeping a proxied object representation over its counterpart on the JVM side.
 
To complete the picture, there is also some magic happening for handling types differences (casting)
and method overloading (that is not supported by PHP). 
 
### Performance and best practices
 
> The following benchmarks does not intend to prove anything but might help understand
> the possible overheads when using the bridge. They were designed to illustrate the
> cost of creating objects and calling methods.   

Machine: Laptop i7-6700HQ 2.60GHz, Tomcat8, Japha 0.14, OracleJDK8, Xenial, php7.0-fpm
Test script: [simple_benchmark.php](./test/bench/simple_benchmarks.php)
Connection time: `$ba = new BridgeAdapter([])` varies between around 2ms (php7.0-fpm) and 5ms (php7.0-cli)

| Benchmark name |  x1 | x100 | x1000 | x10000 | Average | Memory |
|----| ----:|----:|----:|----:|-------:|----:| 
| New java(`java.math.BigInteger`, 1) | 0.24ms| 7.37ms| 38.50ms| 309.74ms|0.0321ms|12.29Kb|
| Method call `java.lang.String->length()` | 0.05ms| 2.37ms| 22.68ms| 219.08ms|0.0220ms|0.34Kb|
| Method call `java.lang.String->concat("hello")` | 0.09ms| 2.90ms| 28.60ms| 284.81ms|0.0285ms|2.09Kb|
| Pure PHP: call PHP strlen() method | 0.00ms| 0.00ms| 0.01ms| 0.08ms|0.0000ms|0.37Kb|
| Pure PHP: concat '$string . "hello"'  | 0.00ms| 0.00ms| 0.02ms| 0.22ms|0.0000ms|120.37Kb|
    
The figures above will vary between systems, but intuitively you might get a glimpse about how
the bridge is sensitive to the number of object creations and method calls: 

> (connection time) + (number of created objects) + (number of methods) + (eventual result parsing).

Imagine a typical case with 10 objects instantiations and 50 method calls (from the PHP side):
 
> 2ms (connection) + 10 * 0.0321ms (new objects) + 50 * 0.0285ms (method) = 3.5ms minimal overhead (looks fine).   

Imagine a heavy case with 1000 new objects and 5000 method calls: 

> 2ms (connection) + 1000 * 0.0321ms (new objects) + 5000 * 0.0285ms (method) = 176ms overhead (looks too big).   

The second example should be avoided if performance matters., but the first one looks not
only viable but a (micro-)service would probably not do better (parsing the result
might give differences - a json_decode() vs parsing bridge response... But eventually you 
can also get the json from the bridge as well).


## Compatibility layer with legacy versions

If you rely on previous implementations of the PHPJavaBridge (the `Java.inc` client), 
have a look to [legacy compatibility guide](./doc/pjb62_compatibility.md) which can help
to gives some tips for migrations.


## Future ideas

- Original code improvements
  - Achieve at least 80% of unit testing for legacy code.
  - Refactor as much as possible and remove dead code.

- Supporting more drivers or techs
  - Drop XML protocol in favour of protocal buffers [GRPC](http://www.grpc.io/) 

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


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

$hashMap = $ba->java('java.util.HashMap', [         
        'message' => 'Hello world',                 
        'params' => [ 0.2, 3.2, 4, 18.12 ]
]);

$hashMap->put('message', '你好，世界');
echo $hashMap->get('message');

$reader = $ba->java('java.io.BufferedReader',
            $ba->java('java.io.FileReader', './var/stats.txt')                        
        );

$javaLib = $ba->java('an.arbitrary.JavaLibraryClass', $myParam=10);

$jResults = $javaLib->processHugeProcessOnJVM($reader, $hashMap->get('params'));

foreach ($jResults as $key => $values) {    
    echo "$key: " . DateTime::createFromFormat('Y-m-d', (string) $values[0]);    
}

```

Behind the scenes, it communicates with a [PHP/Java bridge](https://github.com/belgattitude/php-java-bridge) server which exposes the JVM 
through a specific network protocol. This way all libraries registered on the JVM can be used from PHP, almost just like you could write code
in Java. The Java code is still executed on the JVM, results being sent back to PHP transparently. 
      
## Use cases 

Whenever you need to communicate transparently with the JVM, more specifically with its rich set 
of libraries *(i.e. Jasper Reports, POI, iText, PDFBox, Android, Machine Learning...)* or simply 
establish a bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun :) 

*See also the [considerations, performance and best pratices](https://github.com/belgattitude/soluble-japha#user-content-considerations) before
implementing a solution based on the bridge.*
  
## Features

- Write Java from PHP (with a little extra php-style ;)  
- [Function oriented](https://github.com/belgattitude/soluble-japha#user-content-considerations) solution (vs REST resource oriented)
- Compatible with [PHP/Java bridge](https://github.com/belgattitude/php-java-bridge) server implementation.
- Native communication with the JVM ([JSR-223](https://en.wikipedia.org/wiki/Scripting_for_the_Java_Platform) spec).
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
   $ git clone https://github.com/belgattitude/pjb-starter-springboot // or dowload release an unzip
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

Access the static Java system information.

```php
<?php

// $ba = new BridgeAdapter(...); 

$system = $ba->javaClass('java.lang.System');
echo  $system->getProperties()->get('java.vm_name');

```

### 4. JDBC example

Demonstrate usage of JDBC as it still is a very popular example in Java. 

> Note that iterating over large resultset with the bridge in that way 
> is very expensive in terms of performance. See the [considerations](https://github.com/belgattitude/soluble-japha#user-content-considerations)
> and eventually refer to the [JDBCPerformanceTest.php](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php) test script for
> alternatives. 


Ensure your servlet installation can [locate the JDBC driver](https://github.com/belgattitude/php-java-bridge/tree/master/init-scripts) and try :

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

### Stream example with an SSL client

Demonstrate some possible uses of streams *(code is irrelevant from a PHP point of view)*.

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

The bridge can be considered as a `function oriented` solution (like RPC) in comparison to 
`resource oriented` ones (like REST,...). 
 
From RPC-based solutions *(like XMLRPC, JsonRPC or [gRPC](https://github.com/grpc/grpc))*, 
the bridge does not require a *contract* to be written and hide the complexity of managing the state. 
Practically you can use all Java classes available on the JVM side (libraries) while 
keeping control of their execution at the code level.  

While RPC or REST are more typical solutions and should be considered
first (i.e an high-scale context), the bridge still offer a fast, efficient and reliable 
opportunity to expand PHP to the java ecosystem, publish php-wrapper-libs, connect
Java systems, libraries or drivers...  

In short, **the bridge shines** whenever you need to use directly a Java library without the need
of writing a service layer on the Java side. Java classes are the API (think about
JasperReports, POI, CoreNLP, Jsoup, Android, Machine learning... ready to consume libs).

Of course this level of freedom comes with a certain cost in term of performance and 
**the main weakness of the bridge** lies in the number of method calls between runtimes.
And while calling a method is insignificant (a `roundtrip` is generally less than 1ms), if 
you intend to loop over big structures and call thousands of methods, objects 
the advantages of freedom can quickly become a limitation (see the 'performance' and 'how it works' below). 
Some [quirks](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php) exists but are still far from intuitive.

*Note: Regarding the requirement of installing a JavaBridge server which can be seen as difficult.
Be aware that with recent versions of the [php-java-bridge](https://github.com/belgattitude/php-java-bridge) fork 
and the skeleton [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot) creating a custom taylor-made build
can be done in minutes and even automated in few (bash-)shell commands* 

        
### How it works

The bridge operates by forwarding each Java object instantiations and method calls 
through the connection tunnel (`BridgeAdapter`). 

You can think about it like a database connection on which you execute tiny queries, but
with some differences: 

The protocol used between Java and PHP is based on HTTP and serialized in XML. 
Here's what would be transmitted if you call `$ba->javaClass('myJClass')->aJMethod(2)`:
    
```xml
<c value="myJClass" p="Class"></c>
<i value="0" method="aJMethod" p="Invoke"><object value="2"/></i>
```    

In addition to this, object state is *automatically* maintained between both Java and PHP runtimes.
The PHP client keeping a proxied object representation over its counterpart on the JVM side.
 
To complete the picture, there is also some magic happening for handling types differences (casting)
and method overloading (that is not supported by PHP). 
 
### Performance and best practices
 
> The following benchmarks does not intend to prove anything but might help understand
> the possible overheads when using the bridge. They were designed to illustrate the
> cost of creating objects and calling methods (roundtrips).   

Machine: Laptop i7-6700HQ 2.60GHz, Tomcat8, japha 1.0.0, OracleJDK8, Xenial, php7.0-fpm. 
Test script: [simple_benchmark.php](./test/bench/simple_benchmarks.php). 
Connection time: `$ba = new BridgeAdapter([])` varies between around 2ms (php7.0-fpm) and 5ms (php7.0-cli)

| Benchmark name |  x1 | x100 | x1000 | x10000 | Average | Memory |
|----| ----:|----:|----:|----:|-------:|----:| 
| New java(`java.lang.String`, "One") | 0.10ms| 4.28ms| 36.10ms| 286.22ms|0.0294ms|12.37Kb|
| New java(`java.math.BigInteger`, 1) | 0.24ms| 7.37ms| 38.50ms| 309.74ms|0.0321ms|12.29Kb|
| Method call `java.lang.String->length()` | 0.05ms| 2.37ms| 22.68ms| 219.08ms|0.0220ms|0.34Kb|
| Method call `java.lang.String->concat("hello")` | 0.09ms| 2.90ms| 28.60ms| 284.81ms|0.0285ms|2.09Kb|
| $a = `...String->concat('hello')` . ' world' | 0.11ms| 6.23ms| 58.94ms| 572.52ms|0.0575ms|0.42Kb|
| New java(`java.util.HashMap`, $arr) | 0.14ms| 4.04ms| 42.04ms| 407.97ms|0.0409ms|67.12Kb|
| Method call `HashMap->get('arrKey')` | 0.06ms| 2.49ms| 29.97ms| 299.10ms|0.0299ms|0.33Kb|
| Call `(string) HashMap->get('arrKey')[0]` | 0.12ms| 8.94ms| 87.57ms| 831.70ms|0.0836ms|0.34Kb|
| New `java(HashMap(array_fill(0, 100, true)))` | 0.23ms| 15.50ms| 134.13ms| 1,238.97ms|0.1251ms|1.48Kb|
| Pure PHP: call PHP strlen() method | 0.00ms| 0.00ms| 0.01ms| 0.08ms|0.0000ms|0.37Kb|
| Pure PHP: concat '$string . "hello"'  | 0.00ms| 0.00ms| 0.02ms| 0.22ms|0.0000ms|120.37Kb|
    
*Memory and average time are computed on the 11101 iterations (x1, x100...). Memory does not include the JVM side,
that explains differences from pure php tests and Java one.*      
    
The figures above will vary between systems, but intuitively you might get a glimpse about how
the bridge is sensitive to the number of object creations and method calls (roundtrips): 

> (connection time) + (number of created objects) + (number of methods) + (eventual result parsing).

Imagine a quite complex case with 100 objects instantiations and 100 method calls (from the PHP side):
 
> 2ms (connection) + 7.37ms (100 new objects) + 2.90ms (100 concat methods) = +/- 12ms minimal overhead (looks fine).   

Imagine a heavy case with 1000 new objects and 10000 method calls: 

> 2ms (connection) + 38.5ms (1000 new objects) + 284.81ms (10000 concat methods) = +/- 325ms overhead (looks too big).   

The second example should be avoided if performance matters, but the first one looks not
only viable but a (micro-)service would probably not do better (parsing the result
might give differences - a json_decode() vs parsing bridge response... But eventually you 
can also get the json from the bridge as well).

As an example, generating a report with Jasper will not even require more than 10 objects and
at max 100 method calls. The overhead here is clearly insignificant. 
   
### Some optimizations techniques

#### Using `values` function

You can use the `$ba->getDriver()->value()` method to quickly get PHP normalized values from a Java object (one roundtrip).


```php
<?php

$arrOfArray = [
    'real' => true,
    'what' => 'Too early to know',
    'count' => 2017,
    'arr10000' => array_fill(0, 10000, 'Hello world')
];

$hashMap = $ba->java('java.util.HashMap', $arrOfArray);
$arrFromJava = $ba->getDriver()->values($hashMap);

// $arrOfArray is identical from $arrFromJava (one roundtrip) 
```

#### Optimizing loops

One of many techniques to solve loop/iterations issues (increase rountrips) is to build
an ArrayList, Linked list on the Java side instead of iterating from the PHP side.    

WIP: see the [JDBCPerformanceTest](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php).

## Compatibility layer with legacy versions

If you rely on previous implementations of the PHPJavaBridge (the `Java.inc` client), 
have a look to [legacy compatibility guide](./doc/pjb62_compatibility.md) which can help
to gives some tips for migrations.


## Future ideas

Short term

- [ ] Achieve at least 80% of unit testing for legacy code.
- [ ] Remove obsolete code from PJB62 driver (will also increase coverage)
- [ ] Work on performance

Experiments

- [ ] Improve proxy and use of [ProxyManager](https://github.com/Ocramius/ProxyManager)
- [ ] Drop XML protocol in favour of protocol buffers or [GRPC](http://www.grpc.io/) 
- [ ] Create a JSR-223 php extension in Go, like this [experiment](https://github.com/do-aki/gophp_sample)

### Credits

* This code is principally developed and maintained by [Sébastien Vanvelthem](https://github.com/belgattitude).
* Special thanks to [all of these awesome contributors](https://github.com/belgattitude/soluble-japha/network/members)
* This project is based on the Java.inc work made by the [PHPJavaBridge developers](http://php-java-bridge.sourceforge.net/pjb/contact.php#code_contrib). 
  
## Coding standards and interop

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)


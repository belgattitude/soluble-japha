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


### Considerations

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
the advantages of freedom can quickly become its weakness (see the 'performance' and 'how it works' below). 
Some [solutions](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php) exists but are still far from intuitive.

*Note: Regarding the requirement of installing a JavaBridge server which can be seen as difficult.
Be aware that with recent versions of the [php-java-bridge](https://github.com/belgattitude/php-java-bridge) fork 
and the skeleton [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot) creating a custom taylor-made build
can be done in minutes and with few cli commands* 

        
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
| New `java(HashMap(array_fill(0, 100, true))) | 0.23ms| 15.50ms| 134.13ms| 1,238.97ms|0.1251ms|1.48Kb|
| Pure PHP: call PHP strlen() method | 0.00ms| 0.00ms| 0.01ms| 0.08ms|0.0000ms|0.37Kb|
| Pure PHP: concat '$string . "hello"'  | 0.00ms| 0.00ms| 0.02ms| 0.22ms|0.0000ms|120.37Kb|
    
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

You can use the `$ba->getDriver()->value($arrOfArray)` to quickly get a
a PHP normalized values from a Java object. (one roundtrip).


```php
<?php

$arrOfArray = [
    'real' => true,
    'what' => 'nothing',
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


### Differences with the legacy Java.inc client.

The original `Java.inc` client has been completely refactored to fit current trends modern in PHP practices (2016).

- [x] New API (not backward compatible)

    All global functions have been removed (`java_*`) in favour of a more object oriented approach. 
    By doing so, the new API breaks compatibility with existing code (see the 
    [legacy compatibility guide](./pjb62_compatibility.md) if you have code written against 
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


### Credits

* This code is principally developed and maintained by [Sébastien Vanvelthem](https://github.com/belgattitude).
* Special thanks to [all of these awesome contributors](https://github.com/belgattitude/soluble-japha/network/members)
* This project is based on the Java.inc work made by the [PHPJavaBridge developers](http://php-java-bridge.sourceforge.net/pjb/contact.php#code_contrib). 

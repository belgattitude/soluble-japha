
## Performance

!!! note
    The following benchmarks are given "as is" and might help understand
    the possible overheads when using the bridge. They were designed to illustrate the
    cost of creating objects and calling methods (called `roundtrips`).   

Machine: Laptop i7-6700HQ 2.60GHz, Tomcat8, japha 1.0.0, OracleJDK8, Xenial, php7.0-fpm. 
Test script: [simple_benchmark.php](https://github.com/belgattitude/soluble-japha/blob/master/test/bench/simple_benchmarks.php). 
Connection time: `$ba = new BridgeAdapter([])` varies between around 2ms (php7.0-fpm) and 5ms (php7.0-cli)

| Benchmark name |  x1 | x100 | x1000 | x10000 | Average | Memory |
|----| ----:|----:|----:|----:|-------:|----:| 
| java(`java.lang.String`, "One") | 0.10ms| 4.28ms| 36.10ms| 286.22ms|0.0294ms|12.37Kb|
| java(`java.math.BigInteger`, 1) | 0.24ms| 7.37ms| 38.50ms| 309.74ms|0.0321ms|12.29Kb|
| call `java.lang.String->length()` | 0.05ms| 2.37ms| 22.68ms| 219.08ms|0.0220ms|0.34Kb|
| call `java.lang.String->concat("hello")` | 0.09ms| 2.90ms| 28.60ms| 284.81ms|0.0285ms|2.09Kb|
| $a = `...String->concat('hello')` . ' world' | 0.11ms| 6.23ms| 58.94ms| 572.52ms|0.0575ms|0.42Kb|
| java(`java.util.HashMap`, $arr) | 0.14ms| 4.04ms| 42.04ms| 407.97ms|0.0409ms|67.12Kb|
| call `HashMap->get('arrKey')` | 0.06ms| 2.49ms| 29.97ms| 299.10ms|0.0299ms|0.33Kb|
| call `(string) HashMap->get('arrKey')[0]` | 0.12ms| 8.94ms| 87.57ms| 831.70ms|0.0836ms|0.34Kb|
| java(`.HashMap(array_fill(0, 100, true)))` | 0.23ms| 15.50ms| 134.13ms| 1,238.97ms|0.1251ms|1.48Kb|
| PurePHP: call strlen() method | 0.00ms| 0.00ms| 0.01ms| 0.08ms|0.0000ms|0.37Kb|
| PurePHP: '$string . "hello"'  | 0.00ms| 0.00ms| 0.02ms| 0.22ms|0.0000ms|120.37Kb|
    

!!! info

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


## Behind the scenes
    

The bridge operates by forwarding each Java object instantiations and method calls 
through a maintained connection tunnel with the JavaBridge server. 

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


        

   
### Some optimizations techniques

#### Using `values` function

You can use the `$ba->getDriver()->value()` to quickly get PHP normalized values from a Java object. (one roundtrip).

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

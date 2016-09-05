## Introduction

**soluble-japha** provides a client to write Java code transparently in regular PHP. 

Originally based on the robust and mature [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) project, 
**soluble-japha** leverage the initial client implementation by offering a more modern approach 
*(namespaces, psr standards, php7 ready...)* and support composer for easy dependency requirement 
in a PHP project or your next creative Java-based wrapper library. 
 
### Use cases 

It differs from the idea of api, microservices... where communication requires a contract 
between the client and server in order to exchange information. With soluble-japha, 
you can transparently use Java objects, methods... which will be proxied to the JVM.  
  
With that in mind you can use it to broaden the PHP possibilities to the Java ecosystem and its bunch 
of compelling libraries *(i.e. Jasper Reports, Apache POI, iText, PDFBox, DeepLearning...)* or simply 
establish the bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun.

### Features

- Provides the core to access the Java world from PHP. 
- Client API for [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) server implementation of the [JSR-223](https://en.wikipedia.org/wiki/Scripting_for_the_Java_Platform) spec.
- Use a fast XML-based network protocol behind the scenes. *(no JVM startup efforts)*
- For support with original `Java.inc` client, see the [legacy compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat).
- MIT opensource license.

### Requirements

- PHP 5.5+, 7.0+ or HHVM >= 3.2.
- Installed [JRE or JDK 7+](./server/install_java.md).
- A PHP-Java bridge server [installed](./quick_install.md).

### Basic example

Here's a meaningless code snippet for the impatient :

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
]);

// An utf8 string
$string = $ba->java('java.lang.String', "保éà");
$hash   = $ba->java('java.util.HashMap', ['key1' => $string, 'key2' => 'hello']);
echo $hash->get('key1'); // prints "保éà"
echo $hash->get('key2')->length(); // prints 4

// Some maths
$bigint = $ba->java("java.math.BigInteger", 1);
echo $bigint->intValue() + 10; // prints 11

```



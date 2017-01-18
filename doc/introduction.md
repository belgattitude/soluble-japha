## Introduction

In short **soluble-japha** allows to write Java code in PHP and interact with the JVM and its huge ecosystem. 
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
  
Practically it works by communicating with a [PHP/Java bridge](https://github.com/belgattitude/php-java-bridge) server which exposes the JVM 
through a specific network protocol. This way all libraries registered on the JVM can be used from PHP, almost just like you could write code
in Java. The Java code is still executed on the JVM but send results back to PHP. 
      
## Use cases 

It differs from the idea of api, microservices... where communication requires a contract 
between the client and server in order to exchange information. With soluble-japha, 
you can transparently use Java objects, methods... which will be proxied to the JVM.  
  
With that in mind you can use it to broaden the PHP possibilities to the Java ecosystem and its bunch 
of compelling libraries *(i.e. Jasper Reports, Apache POI, iText, PDFBox, Machine Learning...)* or simply 
establish the bridge whenever a pure-PHP alternative does not exists, reveals itself nonviable 
or just for the fun.
 
## Features

- Write Java from PHP (with a little extra php-style ;)  
- Compatible with [PHP/Java bridge](https://github.com/belgattitude/php-java-bridge) server implementation.
- Efficient, no startup effort, native communication with the JVM ([JSR-223](https://en.wikipedia.org/wiki/Scripting_for_the_Java_Platform) spec).
- Java objects, methods calls... are proxied to the server through a fast XML-based network protocol. 
- *For support with older `Java.inc` client, see the [legacy compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat).*

## Requirements

- PHP 5.5+, 7.0+, 7.1+ or HHVM >= 3.2.
- Installed [JRE or JDK 7+](./doc/server/install_java.md).
- A PHP-Java bridge server [installed](./doc/quick_install.md).





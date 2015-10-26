# PHP/Java bridge client compatibility 

## Introduction

`Soluble\Japha\Bridge\Pjb62` driver provides a compatibility layer for existing 
or legacy code running the original [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) implementation (`Java.inc`).



## Enable compatibility layer

- Compatibility layer is enabled through the 'load_pjb_compatibility' option in the `Bridge\Adapter`.

- The original PHP\Java bridge (`Java.inc`) must not be loaded anymore, 
  you can disable any calls to `include(.../Java.inc)` in your code and 
  replace by the bridge adapter initialization.

- Any previous PJB constants settings will be ignored `JAVA_HOSTS`, `JAVA_SERVLET`...
  pass them as `Bridge\Adapter` options.


```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$servlet_address = 'localhost:8083/servlet.phpjavabridge';

$ba = new BridgeAdapter([
    'driver' => 'Pjb62',
    'servlet_address' => $servlet_address,
    'load_pjb_compatibility' => true
]);
```


## Original API usage

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62',
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
    'load_pjb_compatibility' => true
]);

$bigint = new Java("java.math.BigInteger", 1);
$system = java_class('java.lang.System);

java_instanceof($bigint, 'java.math.BigInteger'); // -> true
java_inspect($bigint); 
java_values($bigint);
//java_invoke();

```

## Migration mappings


### Constants

|Constant                    | Example                                   |
|----------------------------|-------------------------------------------|
| `JAVA_HOSTS`               | `define("JAVA_HOSTS", "127.0.0.1:8787");` |
| `JAVA_SERVLET`             | `define ("JAVA_SERVLET", "/MyWebApp/servlet.phpjavabridge"); |
| `JAVA_PREFER_VALUES`       | `define ("JAVA_PREFER_VALUES", 1);` |
| `JAVA_LOG_LEVEL`           | ? - wip |
| `JAVA_DISABLE_AUTOLOAD`    | int - wip |
| `JAVA_SEND_SIZE`           | int (8192) ? - wip |
| `JAVA_RECV_SIZE`           | int (8192) ? - wip |


### Initialization

|Old                         | New                      |
|-------------------------------|-------------------------------------------|
|`include(... /Java.inc)`       | `$ba = new Bridge\Adapter($option);` |


### API

The following table maps old and new recommended API.

|Legacy                                           | `Bridge\Adapter` ($ba)                      |
|-------------------------------------------------|-------------------------------------------|
|`new Java($class, $args=null)` : `Java`          | `$ba->java($class, $args=null)` : `Interfaces\JavaObject`          |
|`java_class($class)` : `JavaClass`               | `$ba->javaClass($class)` `Interfaces\JavaClass`                |
|`java_instanceof($object, $class)` : `boolean`   | `$ba->isInstanceOf($object, $class)` : `boolean`    |



(under review, soon to be implemented)

|Legacy                          | `Bridge\Adapter` ($ba)                      |
|-----------------------------------|------------------------------------------|
|`java_values($object)` : `mixed`                 | `$ba->getValues($object)` : `mixed`               |
|`java_invoke($object, $method, $args=null)` : `mixed|null` | `$ba->invokeMethod($object, $method, $args=null) : `string\null`  |
|`java_inspect($object)` : `string`               | `$ba->debug()->inspect($object)` : `string`               |
|`getLastException` : `Exception` | `$ba->debug()->getLastException() : `Exception`  |
|`clearLastException` | `$ba->debug()->clearLastException()  |


## Refactoring guidelines

Keep a step by step approach... you can use both API at the same time.

1. Try to change intialization sequence 


## Motivation to refactor


## References

See the `Soluble\Japha\Bridge\Driver\Pjb62\Compat\pjb_functions.php` for more detail.

# Connecting to the bridge

## Introduction

Connection to the bridge is made through the `Soluble\Japha\Bridge\Adapter`object 
and should be initialized once *(similarly to a database connection)*. 

!!! tip
    Using a `container-interop` compatible container like
    [zend-servicemanager](https://github.com/zendframework/zend-servicemanager) 
    is encouraged.

## Example

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use Soluble\Japha\Bridge\Exception as BridgeException;

$options = [
    'driver' => 'Pjb62',  // actually the protocol version 
    'servlet_address' => 'localhost:8080/MyJavaBridge/servlet.phpjavabridge'
];

try {
    $ba = new BridgeAdapter($options);    
} catch (BridgeException\ConnectionException $e) {  
    // Server is not reachable
    echo $e->getMessage();
} 
```

## Parameters 

### Connection params

The `Soluble\Japha\Bridge\Adapter` constructor require `$options`, an associative array with : 

| Parameter        | Description                              |
|------------------|------------------------------------------|
|`driver`          | Currently only 'Pjb62' is supported *(protocol)*. Compatible with php-java-bridge 6.2+ and 7.0+ |
|`servlet_address` | Servlet address: &lt;host&gt;:&lt;port&gt;/&lt;uri&gt;     |

!!! tip
    The `servlet_address` &lt;uri&gt; should ends with the 'servlet.phpjavabridge' file,
    i.e: 'localhost:8080/path/servlet.phpjavabridge'.  


| Advanced params     | Description                              |
|---------------------|------------------------------------------|
|`java_send_size`     | Socket write buffer, by default `8192`. |
|`java_recv_size`     | Socket read buffer, by default `8192`. |
|`java_log_level`     | To enable java side logging level, by default `null`. |
|`java_prefer_values` | By default `true`, see warning below. |

!!! warning
    In short, setting `java_prefer_value` to `false` should theoretically give
    more performance at the cost of some more boilerplate in the code. As the perf 
    improvements have not been measured in practice (yet), the default is `true` in the
    `soluble-japha` implementation. Better to not change it as it will bc-break
    you code.  

### Optional PSR-3 logger

Optionally you can send any PSR-3 logger as the second parameter, for example with monolog :
  
```php
<?php

//...
use Soluble\Japha\Bridge\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$options = [
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
];

$log = new Logger('name');
$log->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

try {
    $ba = new BridgeAdapter($options, $logger);    
} catch (Exception\ConnectionException $e) {
  
    // The error has been logged in your log file, check for
    // "[soluble-japha] Cannot connect to php-java-bridge server (...)"

} 
```
  
## Errors and exceptions 

During initialization with the BridgeAdapter, the following exceptions could happen :

| ExceptionClass                           | Description                 |
|------------------------------------------|-----------------------------|
|`Soluble\Japha\Bridge\Exception\ConnectionException`        | Server not available        |
|`Soluble\Japha\Bridge\Exception\ConfigurationException`     | Invalid parameter           |
|`Soluble\Japha\Bridge\Exception\UnsupportedDriverException` | No valid driver             |
|`Soluble\Japha\Bridge\Exception\InvalidArgumentException`   | Connection params not an array   |


!!! note
    The `Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException` can be thrown
    in case of failure during communication. See your server (tomcat or standalone) logs for detail if
    it happens. Also note that this exception could be thrown when the provided servlet address 
    points to a service different than the bridge. Check it first.




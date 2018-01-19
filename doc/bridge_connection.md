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
    'servlet_address' => 'http://localhost:8080/MyJavaBridge/servlet.phpjavabridge'
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

The `Soluble\Japha\Bridge\Adapter` constructor requires `$options`, an associative array with: 

| Parameter        | Description                              |
|------------------|------------------------------------------|
|`servlet_address` | In the form: `http(s)://<host>:<port>/<context_uri>/servlet.phpjavabridge`     |
|`use_persistent_connection`     | Since @2.5.0. By default `false`, set `true` for better connection times if needed. |

!!! tip
    Since v2.4.0, you can also provide basic auth in the `servlet_address`, i.e.
    `http://user:password@localhost:8083/JavaBridge/servlet.phpjavabridge`.  


| Advanced params     | Description                              |
|---------------------|------------------------------------------|
|`java_send_size`     | Socket write buffer, by default `8192`. |
|`java_recv_size`     | Socket read buffer, by default `8192`. |
|`java_log_level`     | To enable java side logging level, by default `null`. |
|`force_simple_xml_parser` | By default `false`: force the Use the php xml parser instead of native xml_parser(). |
|`driver`             | Defaults to `pjb62` driver implementation.      |
|`java_prefer_values` | By default `true`, see warning below. |


!!! warning
    In short, setting `java_prefer_value` to `false` should theoretically give
    more performance at the cost of some more boilerplate in the code. As the perf 
    improvements have not been measured in practice (yet), the default is `true` in the
    `soluble-japha` implementation. Better to not change it as it will bc-break
    your code.  
    
!!! note
    The `force_simple_xml_parser` param can be set to `true` to force usage of the
    pure-php implementation of the xml parser. This can fix possible issues with
    the native xml parser when the size of an xml message exceeds 10M. 
    Before applying this feature, always check whether it's your only option... 
              

### Optional PSR-3 logger

Optionally you can send any PSR-3 logger as the second parameter, for example with monolog :
  
```php
<?php

//...
use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use Soluble\Japha\Bridge\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$options = [
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
];

$logger = new Logger('name');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

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
|`Soluble\Japha\Bridge\Exception\ConnectionException`        | Server not available *(network port is unreachable)*     |
|`Soluble\Japha\Bridge\Exception\AuthenticationException`    | Invalid credentials given in basic auth *(check config)*   |
|`Soluble\Japha\Bridge\Exception\ConfigurationException`     | Invalid connection parameter *(check config)*          |
|`Soluble\Japha\Bridge\Exception\UnsupportedDriverException` | Specified driver is not supported *(check config)*             |
|`Soluble\Japha\Bridge\Exception\InvalidArgumentException`   | Invalid argument in constructor *(check usage)*   |

!!! warning
    To provide faster initialization, soluble-japha does not deeply check the connection and
    consider a running http(s) port as valid. This can lead to confusion if your connection
    params points to a different running servlet. In this case the `ConnectionException` won't be
    thrown but you'll experience a `Soluble\Japha\Bridge\Exception\BrokenConnectionException`
    when calling java objects. 
     
    A classic example: you forgot to include the servlet uri in your connection params. Instead
    of setting `http://localhost:8080/MyJavaBridge/servlet.phpjavabridge`, you've passed
    `http://localhost:8080/servlet.phpjavabridge`. The connection will succeed *(no `ConnectionException` will be thrown)*
    because there's a listening server. But once you'll call a method on the bridge you'll
    end up with the `BrokenConnectionException`. Fix your config to the correct bridge address.
       
    




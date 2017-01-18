## Connection

Once the [php-java-bridge server](./quick_install.html) is installed and running, you must define
a connection through the `Soluble\Japha\Bridge\Adapter` object. 

### Example

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use Soluble\Japha\Bridge\Exception as BridgeException;

$options = [
    'driver' => 'Pjb62',  
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
];

try {

    $ba = new BridgeAdapter($options);
    
} catch (BridgeException\ConnectionException $e) {
  
    // Server is not reachable
    echo $e->getMessage();

} 
```

### Parameters 

#### Connection parameters

The `Soluble\Japha\Bridge\Adapter` constructor require `$options`, an associative array with : 
 
| Parameter        | Description                              |
|------------------|------------------------------------------|
|`driver`          | Currently only 'Pjb62' is supported      |
|`servlet_address` | Servlet address: &lt;host&gt;:&lt;port&gt;/&lt;uri&gt;     |

*Note that the `servlet_address` &lt;uri&gt; should always indicate the file 
'servlet.phpjavabridge', i.e: 'localhost:8090/servlet.phpjavabridge'. In case of a J2EE deployment 
you must refer to the configured servlet address on you J2EE server (i.e localhost:8080/JavaBridge/servlet.phpjavabridge).* 

#### Optional PSR-3 logger

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
  
 
### Exception 

During intialization with the BridgeAdapter, the following exceptions could happen :

| ExceptionClass                           | Description                 |
|------------------------------------------|-----------------------------|
|`...Exception\ConfigurationException`     | Invalid parameter           |
|`...Exception\UnsupportedDriverException` | No valid driver             |
|`...Exception\InvalidArgumentException`   | Invalid argument in array   |
|`...Exception\ConnectionException`        | Server not available        |

*For clarity replace the "..." by "Soluble\Japha\Bridge\Exception\ ".*

The `Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException` can be thrown
in case of failure during communication. See your logs for detail.

### Bootstrap

The `Bridge\Adapter` should be initialized once. 

Using a `container-interop` compatible container like [zend-servicemanager](https://github.com/zendframework/zend-servicemanager) is encouraged.
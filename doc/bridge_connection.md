## Connection

Once the [php-java-bridge server](./quick_intall.html) is installed and running, you must define
a connection through the `Soluble\Japha\Bridge\Adapter` object. 

### Example

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use Soluble\Japha\Bridge\Exception as BridgeException;

try {
    $ba = new BridgeAdapter([
        'driver' => 'Pjb62', 
        'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
    ]);
} catch (BridgeException\ConnectionException $e) {
  
    // Server is not reachable
    echo $e->getMessage();

} 
```

### Parameters 

The `Soluble\Japha\Bridge\Adapter` constructor require an associative array with : 
 
| Parameter        | Description                              |
|------------------|------------------------------------------|
|`driver`          | Currently only 'Pjb62' is supported      |
|`servlet_address` | Servlet address: &lt;host&gt;:&lt;port&gt;/&lt;uri&gt;     |

If you are using the standalone server, the `servlet_address` &lt;uri&gt; should always be 
set to 'servlet.phpjavabridge', i.e: 'localhost:8089/servlet.phpjavabridge'. In case of a J2EE deployment 
you must refer to the configured servlet address on you J2EE server. 
 
### Exception 

During intialization with the BridgeAdapter, the following exceptions could happen :

| ExceptionClass                           | Description                 |
|------------------------------------------|-----------------------------|
|`...Exception\ConfigurationException`     | Invalid parameter           |
|`...Exception\UnsupportedDriverException` | No valid driver             |
|`...Exception\InvalidArgumentException`   | Invalid argument in array   |
|`...Exception\ConnectionException`        | Server not available        |

*For clarity replace the '...' by 'Soluble\Japha\Bridge\Exception\'.*

### Bootstrap

The `Bridge\Adapter` should be initialized once. Using a service manager is the best.
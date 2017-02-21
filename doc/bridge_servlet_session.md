# Servlet session

If the bridge is deployed on Tomcat (not the standalone version), you can access the servlet session through 
the internal driver:

## Example
 
```php

<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
                           'driver' => 'Pjb62',  
                           'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
                        ]);

$javaSession = $adapter->getDriver()->getJavaSession();

$counter = $javaSession->get('counter');
if ($ba->isNull($counter)) {
    $session->put('counter', 1);
} else {
    $session->put('counter', $counter + 1);
}

```

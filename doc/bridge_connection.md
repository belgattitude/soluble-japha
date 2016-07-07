## Connection

In order to run the soluble-japha client, you must first ensure that java-bridge server is runnning.


### Example

Configure your bridge adapter with the correct driver (currently only Pjb62 is supported) and the PHP-Java-bridge server address.

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
]);
```

 
 
| Exception                         | Description                              |
|-----------------------------------|------------------------------------------|
|`Exception\JavaException`          | Generic java exception                   |
|`Exception\ClassNotFoundException` | A Java class is not found on the jvm side|
|`Exception\NoSuchMethodException`  | Call to an undefined method on the java object | 

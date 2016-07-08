## Exceptions

### Handling Java exceptions

Java exceptions works as regular PHP exceptions. 

Internal Java exceptions extends the `Soluble\Japha\Bridge\Exception\JavaException` class and expose
internal java stack trace as well as corresponding jvm messages through 
the `JavaException::getStackTrace()`, `JavaException::getCause()` methods.

Some common implementations are available in the `Soluble\Japha\Bridge\Exception` namespace.

| Exception                         | Description                              |
|-----------------------------------|------------------------------------------|
|`Exception\JavaException`          | Generic java exception                   |
|`Exception\ClassNotFoundException` | A Java class is not found on the jvm side|
|`Exception\NoSuchMethodException`  | Call to an undefined method on the java object |


```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

// Invalid method
try {
    $string = $ba->java('java.lang.String', "Hello world");
    $string->anInvalidMethod();
} catch (Exception\NoSuchMethodException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
}


// Class not found
try {
    $string = $ba->java('java.INVALID.String', "Hello world");
} catch (Exception\ClassNotFoundException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
} 

// `JavaExceptionInterface` vs php `\Exception` family

$dynamic_var = 'PACKAGE';
try {
    $string = $ba->java("java.$dynamic_var.String", "Hello world");
    throw new \Exception('No error in java String creation');
} catch (Exception\ClassNotFoundException $e) {
    echo "The package $dynamic_var should be 'lang'";
    echo $e->getStackTrace();
} catch (Exception\JavaException $e) {
    echo "An unexpected java exception";
    echo $e->getStackTrace();
} catch (\Exception $e) {
    echo "No Problem at all";
}

```


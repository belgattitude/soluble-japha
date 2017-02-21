# Handling Java exceptions

!!! summary 
    Handling java exceptions works very similarly from PHP the regular ones.
    All java exceptions extends the `Soluble\Japha\Bridge\Exception\JavaException`
    that you can selectively catch in your PHP code.

## JavaException    

All Java exceptions extends the `Soluble\Japha\Bridge\Exception\JavaException` class
and can be catched selectively in your PHP code. 
The JavaException offers two useful methods on top of the standard PHP exception class:
the `JavaException::getStackTrace()` and `JavaException::getCause()` to provide 
Java specific information. Note the existence of the `JavaException::getJavaClassName()` 
method to quickly retrieve the initial object where the exception happened. 

Additionally, some common exceptions have been implemented:
     
| Exception                         | Description                              |
|-----------------------------------|------------------------------------------|
|`Soluble\Japha\Bridge\Exception\JavaException`          | Generic java exception                   |
|`Soluble\Japha\Bridge\Exception\ClassNotFoundException` | A Java class is not found on the jvm side|
|`Soluble\Japha\Bridge\Exception\NoSuchMethodException`  | Call to an undefined method on the java object |


## Example

```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

// Invalid method
try {
    $string = $ba->java('java.lang.String', "Hello world");
    $string->anInvalidMethod();
} catch (Exception\NoSuchMethodException $e) {
    echo $e->getJavaClassName(); 
    echo $e->getMessage();
    echo $e->getStackTrace();
}


// Class not found
try {
    $string = $ba->java('java.INVALID.String', "Hello world");
} catch (Exception\ClassNotFoundException $e) {
    echo $e->getJavaClassName(); 
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
    echo $e->getJavaClassName(); 
    echo $e->getStackTrace();
} catch (\Exception $e) {
    echo "No Problem at all";
}

```


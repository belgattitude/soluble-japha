# Java exceptions

!!! summary 
    Java exception handling is similar to PHP. All java exceptions will
    be converted to a generic `JavaException` one on which you can
    call the additional `getJavaClassName()` and `getStackTrace()` methods, 
    they'll prove useful over time.   
        

## JavaException    

Exception thrown from the JVM will be converted to a `Soluble\Japha\Bridge\Exception\JavaException` 
PHP one. You can catch them like you do in PHP, and use the additionnal methods:
  
| Method                      |  Description          |
|-----------------------------|-----------------------|   
| `$e->getMessage()`          | The exception message |
| `$e->getJavaClassName()`    | The Java exception, i.e. `java.lang.ClassNotFoundException` |
| `$e->getStackTrace()`       | The JVM stack trace  |


## Convenience exception

For convenience, the following exceptions extends the base `JavaException` class.

| Exception                         | Description                              |
|-----------------------------------|------------------------------------------|
|`ClassNotFoundException` | A Java class is not found on the jvm side|
|`NoSuchMethodException`  | Call to an undefined method on the java object |
|`SqlException`  | Invalid SQL exception |


### ClassNotFoundException

The `Soluble\Japha\Bridge\Exception\ClassNotFoundException` is a convenient
exception class thrown whenever a Java class is not found:

```php
<?php
use Soluble\Japha\Bridge\Exception;

try {
    $string = $ba->java('java.INVALID.FQDN', "Hello world");
} catch (Exception\ClassNotFoundException $e) {    
    echo $e->getMessage();
    // -> "java.lang.ClassNotFoundException"
    echo $e->getJavaClassName();
    echo $e->getStackTrace();
} 
```

### NoSuchMethodException

The `Soluble\Japha\Bridge\Exception\NoSuchMethodException` is a convenient 
exception class thrown whenever a method does not exists on an object


```php
<?php
use Soluble\Japha\Bridge\Exception;

// Invalid method
try {
    $string = $ba->java('java.lang.String', "Hello world");
    $string->anInvalidMethod();
} catch (Exception\NoSuchMethodException $e) {
    echo $e->getJavaClassName(); 
    // -> "java.lang.NoSuchMethodException" 
    echo $e->getMessage(); 
    // -> Invoke failed: [[o:String]]->anInvalidMethod. Cause: java.lang.NoSuchMethodException: anInvalidMethod()...
    echo $e->getCause(); 
    // -> java.lang.NoSuchMethodException: anInvalidMethod()...   
    echo $e->getStackTrace();
}

```



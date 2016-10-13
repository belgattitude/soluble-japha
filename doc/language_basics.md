## Language basics

Basically `soluble-japha` relies on two php generic objects: `JavaClass` and `JavaObject`. They will be used
to proxy operations (instanciations, method calls..) to the JVM and maintain state.

This approach ensures that all objects available to the JVM (libraries...) can be managed from the PHP runtime... 
but, by nature, requires a slightly different syntax as well as an understanding of Java/PHP types differences.
               
               
### Working with objects

Whenever you want to work with a Java Object you must instanciate it through 
the `$bridge->java('<JAVA FQDN>', $arg1, $arg2, ...)`. If you look to the following 
example:
 
```php

$ba = new \Soluble\Japha\Bridge\Adapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
]);

$string = $ba->java('java.lang.String', "Hello world");
$hash   = $ba->java('java.util.HashMap', ['key1' => $string, 'key2' => 'hello']);
```



#### Methods

(todo) explain differences for static '::' calls

#### Static

### Scalar Types

Most of the scalar types are automatically casted to their own language support. 


#### NullBool

### Java/PHP scalar types differences

Due to the inherent differences

(todo) detail inherent differences: how to test bool, int, null.

### Java documentation

(todo) tips for reading basic java resources (jvm api...)
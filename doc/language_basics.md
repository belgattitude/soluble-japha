## Language basics

Basically `soluble-japha` relies on two php generic objects: `JavaClass` and `JavaObject`. They will be used
to proxy operations (instanciations, method calls..) to the JVM and maintain state.

This approach ensures that all objects available to the JVM (libraries...) can be managed from the PHP runtime... 
but, by nature, requires a slightly different syntax.

So when writing Java code from PHP, you need to be aware of some inherent differences between
the languages :

1. Java support multiple signatures for methods (and constructor).
2. PHP has a special notation for static method calls (::), Java does not (.).
3. PHP refers to constants with '::', Java does not (.).

But also some limitations due to object proxying : 

1. If most scalar types *(int, string, array...)* are automatically casted, testing an object
on `null` or `booleans` will actually be made on the proxied php object and not the Java 
internal value.  
2. Java `Exception` catched on the PHP side are converted in a generic PHP exception `JavaException`. 
 
*soluble-japha* brings solutions to those problems, but primary reflexes might not work (i.e. you try
to call a static java method through the bridge with '::' instead of the regular '->'...)
   
                              
### Object instanciation

Whenever you want to work with a Java Object you must instanciate it through 
the `$bridge->java('[JAVA_FQDN]', $arg1=null, $arg2=null, ...)`. If you look to the following 
example:
 
```php

$ba = new \Soluble\Japha\Bridge\Adapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
]);

$string = $ba->java('java.lang.String', 'Hello world');
$hash   = $ba->java('java.util.HashMap', ['key1' => $string, 'key2' => 'hello']);
```

*The **[JAVA FQDN]** is the fully qualified java class name (case-sensitive) optionally 
followed by a list of arguments (variadic notation: `BridgeAdapter::java(string $javaClass, ...$args)`).*  

In case of multiple constructor signatures *(PHP does not have)*, you can have a look to the following example :

```
//...
$mathContext = $ba->java('java.math.MathContext', $precision=2);
$bigint = $ba->java('java.math.BigInteger', 123456);
$bigdec = $ba->java('java.math.BigDecimal', $bigint, $scale=2, $mathContext);

```

and look how the [BigDecimal](https://docs.oracle.com/javase/7/docs/api/java/math/BigDecimal.html#constructor_summary) 
constructor has been selected from the provided arguments.  


### Calling methods

(todo) explain differences for static '::' calls

### Class constants

todo) explain differences for static '::' calls


### Testing Null and Boolean

Due to internal proxying between java and php objects, 'null', 'false' and 'true' values 
must be tested through the bridge object. Otherwise the tes is made the php proxied object
and not it's value.

```php
<?php

// $ba = new BridgeAdapter(...); 

$javaBoolean = $ba->java('java.lang.Boolean', true);
if ($ba->isTrue($javaBoolean)) {
    echo "Yes, it is.";
}

$javaBoolean = $ba->java('java.lang.Boolean', false);
if (!$ba->isTrue($javaBoolean)) {
    echo "Yes, it is not.";
}


if (!$ba->isNull($rs)) {
    $rs->close();
}
```

### Scalar Types

Most of the scalar types are automatically casted to their own language support: string, int, array, boolean, ...
 
### Java documentation

(todo) tips for reading basic java resources (jvm api...)
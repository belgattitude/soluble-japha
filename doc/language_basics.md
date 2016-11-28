## Language basics

Basically `soluble-japha` relies on two php generic objects: `JavaClass` and `JavaObject`. They will be used
to proxy operations (instanciations, method calls..) to the JVM and maintain state.

This approach ensures that all objects available to the JVM (libraries...) can be managed from the PHP runtime... 
but, by nature, requires a slightly different syntax.

So when writing Java code from PHP, you need to be aware of some inherent differences between
the languages :

1. Java support [method overloading](https://docs.oracle.com/javase/tutorial/java/javaOO/methods.html) for methods (and constructors).
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

In case of multiple constructor signatures *(PHP does not have constructor overloading)*, you can have a look to the following example :

```
//...
$mathContext = $ba->java('java.math.MathContext', $precision=2);
$bigint = $ba->java('java.math.BigInteger', 123456);
$bigdec = $ba->java('java.math.BigDecimal', $bigint, $scale=2, $mathContext);

```

and look how the [BigDecimal](https://docs.oracle.com/javase/7/docs/api/java/math/BigDecimal.html#constructor_summary) 
constructor has been selected from the provided arguments.  

### Dealing with Java classes

When dealing with Java classes that cannot be instanciated (private constructor), like system classes,
factories, singletons... 

You must first refer the class with the method `$ba->javaClass('[JAVA FQDN]', $arg1=null, ...)` instead of
`$ba->java()`...

Take a look to the following example with (java.lang.System)[https://docs.oracle.com/javase/7/docs/api/java/lang/System.html] class.   

```php
$system = $ba->javaClass('java.lang.System');
echo  $system->getProperties()->get('java.vm_name);
```

A singleton:

```php
$calendar = $ba->javaClass('java.util.Calendar')->getInstance();
$date = $calendar->getTime();
```

### Calling methods

After creating a java object with `$bridge->java('[JAVA_FQDN]', $arg1=null, $arg2=null, ...)` you can call
any public methods on it. Keep it mind that Java supports [method overloading](https://docs.oracle.com/javase/tutorial/java/javaOO/methods.html),
so before calling a method, ensures parameters will match the desired method signature.

For example the [java.lang.String](http://docs.oracle.com/javase/7/docs/api/java/lang/String.html#indexOf(java.lang.String)) object exposes
two methods for `indexOf()`

```php

$javaString = $ba->java('java.lang.String', 'A key is a key!');
$index = $javaString->indexOf('key');
// Will print 2, the selected method is `java.lang.String#indexOf(String str)`

$index = $javaString->indexOf('key', $fromIndex=8);
// Will print 11, the selected method is `java.lang.String#indexOf(String, $fromIndex)`

```



#### Calling static methods

Whenever you want to call a static method, please refer it to the class through 
`$ba->javaClass('[JAVA FQDN]', $arg1=null, ...)` and not the java object. 
Here's an example on the java calendar class (singleton) :

```php
<?php
// $ba = new BridgeAdapter(...); 

$calendarClass = $ba->javaClass('java.util.Calendar');
$calendarInstance = $calendarClass->getInstance();

```

### Invoke method

For dynamic calls, the `Adapter::invoke()` method can be used on JavaObject or
JavaClass objects:

```php
<?php 

$javaString = $ba->java('java.lang.String', 'A key is a key!');
$length = $ba->invoke($javaString, 'length');

$index = $ba->invoke($javaString, 'indexOf', ['key']);
$index = $ba->invoke($javaString, 'indexOf', ['key', $fromIndex=8]);

```

*Be aware that the arguments have to be send as an array which differs from 
a standard method call `$javaString->indexOf('key', $fromIndex=8)`.* 


### Class constants

Constants on java classes are called like regular properties (no `::`).

```php
<?php

// $ba = new BridgeAdapter(...); 

$tzClass = $ba->javaClass('java.util.TimeZone');
echo $tz->getDisplayName(false, $tzClass->SHORT);

```

### Testing Null and Boolean

Due to internal proxy-ing between java and php objects, 'null', 'false' and 'true' values 
must be tested through the bridge object. Otherwise the test is made the php proxied object
and not its value.

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

### Working with dates

Dates are not (yet) automatically casted between Java and PHP. Keep in mind that internally the JVM 
keeps tracks of milliseconds where PHP is limited to microseconds and that timezones might differs between
runtimes.

Ignoring deprecated constructors, the [java.util.Date](https://docs.oracle.com/javase/7/docs/api/java/util/Date.html) allows
creation of dates based on a timestamp expressed in milliseconds : 
 
```php
<?php

// $ba = new BridgeAdapter(...); 

$phpDate = \DateTime::createFromFormat('Y-m-d', '2016-12-21');
$milli = $phpDate->format('U') * 1000; // Internally the JVM handles milliseconds
                                       // In order to create a new Java date, 
                                       // php dates must be converted accordingly.
                                       // The 'U' allows formatting the date as
                                       // microseconds since epoch time, just multiply
                                       // by 1000 to get milliseconds.
                                       // Alternatively you can use 
                                       // $milli = strtotime('2016-12-21') * 1000;  
                                       
$javaDate = $ba->java('java.util.Date', $milli);

$simpleDateFormat= $ba->java("java.text.SimpleDateFormat", 'yyyy-MM-dd');

echo $simpleDateFormat->format($javaDate);

// Will print: "2016-12-21"
```

Alternatively you can use the [java.text.SimpleDateFormatter](https://docs.oracle.com/javase/7/docs/api/java/text/SimpleDateFormat.html) object 
to parse the date string without the php conversion.  

```php
<?php

// $ba = new BridgeAdapter(...); 

$date = '2016-12-21';
$simpleDateFormat = $ba->java("java.text.SimpleDateFormat", 'yyyy-MM-dd');
$javaDate = $simpleDateFormat->parse($date); // This is a Java date
echo $simpleDateFormat->format($javaDate);
// Will print: "2016-12-21"
```

Please be aware that timezones might differ from PHP and the JVM. In that case, dates between PHP and Java
are not guaranteed to be the same (think of 2016-12-31 23:00:00 in London and Paris)
 
In most cases those differences can be easily fixed by ensuring both the JVM and PHP configurations 
use the same timezone. 

Another option is to pass the current timezone in the formatter :

```php
$pattern = "yyyy-MM-dd HH:mm";
$formatter = $ba->java("java.text.SimpleDateFormat", $pattern);
$tz = $ba->javaClass('java.util.TimeZone')->getTimezone("Europe/Paris");
$formatter->setTimeZone($tz);
```
  

   
### Java documentation

(todo) tips for reading basic java resources (jvm api...)

- See the [java api reference](https://docs.oracle.com/javase/7/docs/api/) 
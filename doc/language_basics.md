# Language basics

!!! note

    Coding Java from PHP is relatively similar to an equivalent pure Java code. To 
    avoid confusion while developing you must keep aware that:    
    
    1. Java supports [overloading](https://docs.oracle.com/javase/tutorial/java/javaOO/methods.html) for methods (and constructors).
    2. PHP have the *multipurpose* array for everything, Java does not. See [here](#array-types) to learn more.
    3. DateTime and timezones are handled differenlty, see [here](#working-with-dates) to learn more.
    4. Java supports inner classes. See [here](#inner-classes)          
     
    And remember  
    
    1. While array and scalar types (int, string, bool, float) are automatically casted, 
    testing on `null` and `booleans` requires the use of `$ba->isNull()` and `$ba->isTrue()` 
    methods. See [here](#testing-null-and-booleans) to learn more.
    2. PHP use the `::` for static method calls and constants where Java does not `.`.          
     
    soluble-japha brings solutions to those problems, but primary reflexes might not work (i.e. you try
    to call a static java method through the bridge with '::' instead of the regular '->'...)
     
  
                              
## Object instantiation

Whenever you want to work with a Java Object you must instantiate it through 
the `$ba->java('[JAVA_FQDN]', $arg1=null, $arg2=null, ...)`. 

### Simple constructor

 
```php
<?php
$ba = new \Soluble\Japha\Bridge\Adapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
]);

$string = $ba->java('java.lang.String', 'Hello world');
$hash   = $ba->java('java.util.HashMap', ['key1' => $string, 'key2' => 'hello']);
```

!!! tip
    The **[JAVA FQDN]** is the fully qualified java class name (case-sensitive) optionally 
    followed by a list of arguments (variadic notation: `BridgeAdapter::java(string $javaClass, ...$args)`).*  


### Overloaded constructor

In case of multiple constructor signatures *(PHP does not have constructor overloading)*, look at :

```php
<?php
$mathContext = $ba->java('java.math.MathContext', $precision=2);
$bigint = $ba->java('java.math.BigInteger', 123456);
$bigdec = $ba->java('java.math.BigDecimal', $bigint, $scale=2, $mathContext);

```

!!! tip
    Refer to the [BigDecimal](https://docs.oracle.com/javase/7/docs/api/java/math/BigDecimal.html#constructor_summary) 
    constructor to learn how it has been selected from the provided arguments.  

  
## Methods  

After creating a java object with `$ba->java('[JAVA_FQDN]', $arg1=null, $arg2=null, ...)` you can call
any public methods on it. Keep it mind that Java supports [method overloading](https://docs.oracle.com/javase/tutorial/java/javaOO/methods.html),
so before calling a method, ensures parameters will match the desired method signature.

For example the [java.lang.String](http://docs.oracle.com/javase/7/docs/api/java/lang/String.html#indexOf(java.lang.String)) object exposes
two methods for `indexOf()`

```php
<?php
$javaString = $ba->java('java.lang.String', 'A key is a key!');
$index = $javaString->indexOf('key');
// Will print 2, the selected method is `java.lang.String#indexOf(String str)`

$index = $javaString->indexOf('key', $fromIndex=8);
// Will print 11, the selected method is `java.lang.String#indexOf(String, $fromIndex)`

```

## Classes

Use the `$ba->javaClass('[JAVA FQDN]', $arg1=null, ...)` method instead of
`$ba->java()`...

Take a look to the following example with [java.lang.System](https://docs.oracle.com/javase/7/docs/api/java/lang/System.html) class.   

```php
<?php
$system = $ba->javaClass('java.lang.System');
echo  $system->getProperties()->get('java.vm_name');
```

## Static methods

Static methods are called like regular php methods (no `::`).

```php
<?php
$calendar = $ba->javaClass('java.util.Calendar')->getInstance();
```

!!! tip
    Note the use of `$ba->javaClass(...)` instead of `$ba->java(...)` to refer to
    the java class and call the static method on it. Remember to use it whenever 
    you face a factory, singleton or a generic static method.     


## Constants

Constants on java classes are called like regular properties (no `::`).

```php
<?php

// $ba = new BridgeAdapter(...); 

$tzClass = $ba->javaClass('java.util.TimeZone');
echo $tz->getDisplayName(false, $tzClass->SHORT);

```

## Iterables

!!! warning
    Iterations have a cost on performance, and looping over large
    sets is highly discouraged. See how you can improve speed with
    the [values() method](./language_optimizations.md#values-method).

You can use standard `foreach`, `while`, `for`,... to loop over Java iterable objects (Map, Collection, List...).

```php
<?php

// $ba = new BridgeAdapter(...); 

$properties = $ba->javaClass('java.lang.System')->getProperties();
foreach ($properties as $key => $value) {
    echo "$key: $value\n";
}
```

## Inner classes

Java supports inner classes *(classes as a class property)*. 
To explicitly refer to an inner class, the `FQDN` separator should be a `$` sign instead
of the regular `.`. 

The following example makes use of the [Calendar.Builder](https://docs.oracle.com/javase/8/docs/api/java/util/Calendar.Builder.html) class:
     
```php
<?php

// $ba = new BridgeAdapter(...); 


$builder = $ba->java('java.util.Calendar$Builder');
echo $ba->getClassName($builder); // will print 'java.util.Calendar$Builder'

$calendar = $builder->setCalendarType('gregory')->build();
echo $ba->getClassName($calendar); // will print 'java.util.GregorianCalendar' 
```

!!! warning
    As PHP will interpret the `$` as a variable, be sure to use
    single-quotes to hold the class name.
    

## Datatypes

### Scalar types

The PHP scalar types: `string`, `int`, `float` and `boolean` can be sent
as parameters to Java methods or constructors transparently: 

```php
<?php

$price = 12.99;
$quantity = $ba->java('java.lang.Integer', 10);
$jstring  = $ba->java('java.lang.String', 'Hello world');
```

!!! tip
    Be aware that Java often use object versions of scalars, like
    `java.lang.String`, `java.lang.Integer`, `java.lang.Boolean`...
    In those cases, remember they generally provides methods to
    retrieve the scalar value. For example:      
    
    ```php
    <?php
    $quantity = $ba->java('java.lang.Integer', 10);
    $total = $quantity->intValue() * 12.99;
    echo sprintf('Total id %.2f', $total); // -> 1299.00
    ```

### Array types

The PHP multi-purpose array has not equivalent in Java and you'll often
use Java objects like Map, HashMap, Collection, List, Vector... instead. 

```php
<?php
$array = ['name' => 'John Doe', 'age' => 26];
$hashMap = $ba->java('java.util.HashMap', $array);
```

While you can generally send the parameters as a standard php array, to
get an array back you can use the fast `values()` method:

```php
<?php
// with HashMap
$input_array = ['name' => 'John Doe', 'age' => 26];
$hashMap = $ba->java('java.util.HashMap', $input_array);
$output_array = $ba->values($hashMap);
// $input_array === $output_array

// With ArrayList
$arrayList = $ba->java('java.util.ArrayList');
$arrayList->add('Hello');
$arrayList->add('World');

$array = $ba->values($arrayList->toArray());
// $array == ['Hello', 'World'];

```

or iterate the object (ok for small sets).


### Testing null and booleans

!!! warning
  
    Testing null and boolean:
    
    Due to internal proxying between java and php objects, 'null', 'false' and 'true' values 
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

### Working with dates

!!! warning
    Dates are not (yet) automatically casted between Java and PHP. Keep in mind that 
    
    - Internally the JVM works with milliseconds, PHP with to microseconds (7.1 introduced milli). 
    - Timezones might differs between runtimes. Check your configuration.

As an example, the [java.util.Date](https://docs.oracle.com/javase/7/docs/api/java/util/Date.html) allows
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

### Timezones

Timezones might differ from PHP and the JVM runtimes. In that case, dates between PHP and Java
are not guaranteed to be the same (think of 2016-12-31 23:00:00 in London and Paris)
 
In most cases those differences can be easily fixed by ensuring both the JVM and PHP configurations 
use the same timezone. 

Another option is to pass the current timezone in the formatter :

```php
<?php
$pattern = "yyyy-MM-dd HH:mm";
$formatter = $ba->java("java.text.SimpleDateFormat", $pattern);
$tz = $ba->javaClass('java.util.TimeZone')->getTimezone("Europe/Paris");
$formatter->setTimeZone($tz);
```

## Resources

PHP resources like pointer to a file or a network socket cannot be
exchanged between runtimes.  
       
### IO streams

!!! Warning
    For performance, operations on resources (like iterating over a file)
    is highly discouraged. They should be made on their own environment.

As an example

```php
<?php
$bufferedReader = $ba->java('java.io.BufferedReader',
                        $ba->java('java.io.FileReader', __FILE__)
                  );

```
  


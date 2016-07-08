## Language reference

### Introduction

All examples assumes a BridgeAdapter instance is available.

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
]);
```
 

### Basic Java example

The following example shows how to create and use standard Java objects. 

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8083/servlet.phpjavabridge'
]);

// An utf8 string
$string = $ba->java('java.lang.String', "保éà");
$hash   = $ba->java('java.util.HashMap', ['key1' => $string, 'key2' => 'hello']);
echo $hash->get('key1'); // prints "保éà"
echo $hash->get('key2')->length(); // prints 4

// Java dates
$pattern = "yyyy-MM-dd";
$fmt     = $ba->java("java.text.SimpleDateFormat", $pattern);
echo $fmt->format($ba->java("java.util.Date")); // print today

// Some maths
$bigint = $ba->java("java.math.BigInteger", 1);
echo $bigint->intValue() + 10; // prints 11

```

### Create java objects

To create (instanciate) new Java objects, use the `Bridge\Adapter->java($class, ...$args)` method.

```php
<?php

// $ba = new BridgeAdapter([...]);

$javaString = $ba->java('java.lang.String', "Hello world");
echo $javaString->__toString();     // -> Hello world
echo $javaString;                   // -> Hello world
echo ($javaString instanceof \Soluble\Japha\Interfaces\JavaObject) ? 'true'; 'false'; // -> true
```

In case of multiple constructors, select the constructor signature needed and provide the corresponding arguments: 

```php
<?php

// $ba = new BridgeAdapter([...]); 

$mathContext = $ba->java('java.math.MathContext', $precision=2);
$bigint = $ba->java('java.math.BigInteger', 123456);
$bigdec = $ba->java('java.math.BigDecimal', $bigint, $scale=2, $mathContext);

echo $bigdec->floatValue(); // will print 1200

```
  

### Using java *final* classes

For static classes, use the `Bridge\Adapter->javaClass($class)` method.

```php
<?php

$system = $ba->javaClass('java.lang.System');
echo  $system->getProperties()->get('java.vm_name);

```


### Calling static methods

To call static java methods from PHP, use the `->` as for usual PHP methods :

```php
<?php

// $ba = new BridgeAdapter(...); 

$calendar = $ba->javaClass('java.util.Calendar')->getInstance();
$date = $calendar->getTime();

```


### Class constants

Constants on java classes are called like regular properties (no `::`).

```php
<?php

// $ba = new BridgeAdapter(...); 

$tzClass = $ba->javaClass('java.util.TimeZone');
echo $tz->getDisplayName(false, $tzClass->SHORT);

```

### Type handling


#### Null and boolean values

Due to internal proxying between java and php objects, 'null', 'false' and 'true' values must be tested through the bridge object :

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
 

### Iterations

Java iterable objects can be looped with a simple `foreach`.

```php
<?php

// $ba = new BridgeAdapter(...); 

$properties = $ba->javaClass('java.lang.System')->getProperties()
foreach ($properties as $key => $value) {
    echo "$key: $value\n";
}
```


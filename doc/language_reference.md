## Language reference

### 1. Connection example

Configure your bridge adapter with the correct driver (currently only Pjb62 is supported) and the PHP-Java-bridge server address.

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
    'driver' => 'Pjb62', 
    'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
]);
```
 

### 2. Basic Java example

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

### 3. Create java objects

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
  

### 4. Using java *final* classes

For static classes, use the `Bridge\Adapter->javaClass($class)` method.

```php
<?php

$system = $ba->javaClass('java.lang.System');
echo  $system->getProperties()->get('java.vm_name);

```


### 5. Calling static methods

To call static java methods from PHP, use the `->` as for usual PHP methods :

```php
<?php

// $ba = new BridgeAdapter(...); 

$calendar = $ba->javaClass('java.util.Calendar')->getInstance();
$date = $calendar->getTime();

```


### 6. Class constants

Constants on java classes are called like regular properties (no `::`).

```php
<?php

// $ba = new BridgeAdapter(...); 

$tzClass = $ba->javaClass('java.util.TimeZone');
echo $tz->getDisplayName(false, $tzClass->SHORT);

```

### 7. Type handling


#### 7.1. Null and boolean values

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
 

### 8. Iterations

Java iterable objects can be looped with a simple `foreach`.

```php
<?php

// $ba = new BridgeAdapter(...); 

$properties = $ba->javaClass('java.lang.System')->getProperties()
foreach ($properties as $key => $value) {
    echo "$key: $value\n";
}
```

### 9. Handling Java exceptions

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

### 10. JDBC example

Ensure your servlet installation can locate the JDBC driver and try :

```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

$driverClass = 'com.mysql.jdbc.Driver';
$dsn = "jdbc:mysql://localhost/my_database?user=login&password=pwd";

try {

    $driverManager = $ba->javaClass('java.sql.DriverManager');

    $class = $ba->javaClass('java.lang.Class');
    $class->forName($driverClass);
    
    $conn = $driverManager->getConnection($dsn);

} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
}

try {
    $stmt = $conn->createStatement();
    $rs = $stmt->executeQuery('select * from product');
    while ($rs->next()) {
        $title = $rs->getString("title");
        echo $title;            
    }        
    if (!$ba->isNull($rs)) {
        $rs->close();
    }
    if (!$ba->isNull($stmt)) {
        $stmt->close();
    }
    $conn->close();
} catch (Exception\JavaException $e) {
    //...
}

```

## Compatibility layer
----------------------


Take a look to [legacy compatibility guide](./doc/pjb62_compatibility.md) for more information.


## Original PHPJavaBridge (Java.inc) differences
------------------------------------------------

- New API
  - A fresh new API to allow future drivers and enhancements.
  - A more intuitive and verbose exception handling.
  - No global namespace pollution.
  - For legacy code a compatibility layer can be loaded.

- Performance
  - Reduced memory usage and faster execution.
  - Opcache friendly (no allow_url_open)

- PHPJavaBridge refactorings (Java.inc)
  - PHP 5.3+ namespaces, PSR-4 autoloading and PSR-2 coding style.
  - Removed global variables, functions and unscoped statics.
  - Removed most notices and warnings.
  - No allow_url_open possible (security)
  - Removed deprecated code.

## Future ideas
---------------

- Original code improvements
  - Achieve at least 80% of unit testing for legacy code.
  - Refactor as much as possible and remove dead code.

- Supporting more drivers or techs
  - [Zend Java bridge](http://files.zend.com/help/Zend-Platform/about.htm) driver compatibility.
  - [GRPC](http://www.grpc.io/) 
  - Support the [MethodHandles](http://docs.oracle.com/javase/7/docs/api/java/lang/invoke/MethodHandles.html) and [InvokeDynamic](http://docs.oracle.com/javase/7/docs/api/java/lang/invoke/package-summary.html) APIs described in [JSR-292](https://jcp.org/en/jsr/detail?id=292).

- Improve proxy
  - see [ProxyManager](https://github.com/Ocramius/ProxyManager)

- Explore new possibilities 
  - Create a JSR-223 php extension in Go, like this [experiment](https://github.com/do-aki/gophp_sample)


## Credits
----------

Thanks to the fantastic PHPJavaBridge project leaders and contributors who made it possible. 
See their official homepage on http://php-java-bridge.sourceforge.net/pjb/index.php.

## Coding standards
-------------------

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)







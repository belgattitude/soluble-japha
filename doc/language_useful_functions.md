# Useful functions
                          
## Get the Java classname

To get the fully qualified java class name on an object, simply call:

```php
<?php
$javaString = $this->adapter->java('java.lang.String', 'Hello World');
$javaFQDN = $this->adapter->getClassName($javaString);
// will print 'java.lang.String'
```

## Driver operations

!!! note
    Advanced operations are handled though the `DriverInterface` object, you can
    retrieve the Driver from the Adapter:
    
    ```php
    <?php
    $driver = $this->adapter->getDriver();
    ```

### Getting values

!!! tip
    Using the DriverInterface::value() method gives best performance than
    equivalent operations as it gets the result in one step (one roundtrip)

You can use the `$ba->getDriver()->value($arrOfArray)` to quickly 
get PHP normalized values from a Java object.

```php
<?php

$arrOfArray = [
    'real' => true,
    'what' => 'Too early to know',
    'count' => 2017,
    'arr10000' => array_fill(0, 10000, 'Hello world')
];

$hashMap = $ba->java('java.util.HashMap', $arrOfArray);
$arrFromJava = $ba->getDriver()->values($hashMap);

// $arrOfArray is identical from $arrFromJava (one roundtrip) 
```
      

### Inspect a JavaObject
  
To inspect the content of a Java object, you can call the inspect method on the Driver:
  
```php
<?php
$javaString = $ba->java('java.lang.String', 'Hello World');
echo $ba->getDriver()->inspect($javaString);
// will print
// [class java.lang.String:
// Constructors:
//  public java.lang.String(byte[],int,int)
//  public java.lang.String(byte[],java.nio.charset.Charset)
//  public java.lang.String(byte[],java.lang.String) throws java.io.UnsupportedEncodingException
//  public java.lang.String(byte[],int,int,java.nio.charset.Charset)
// ...
  
```
  
### Dynamic method invocation

For dynamic calls, the `DriverInterface::invoke()` method can be used on JavaObject or
JavaClass objects:

```php
<?php
$javaString = $ba->java('java.lang.String', 'A key is a key!');
$length = $ba->getDriver()->invoke($javaString, 'length');

$index = $ba->getDriver()->invoke($javaString, 'indexOf', ['key']);
$index = $ba->getDriver()->invoke($javaString, 'indexOf', ['key', $fromIndex=8]);
```

!!! note
    Be aware that the arguments have to be send as an array which differs from 
    a standard method call, compare it to `$javaString->indexOf('key', $fromIndex=8)`.
    for an example. 
  

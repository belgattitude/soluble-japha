# Useful functions
   
## Type related
                          
## Java classname

To get the fully qualified java class name on an object, simply call:

```php
<?php
$javaString = $ba->java('java.lang.String', 'Hello World');
$javaFQCN = $ba->getClassName($javaString);
// will print 'java.lang.String'
```

## InstanceOf

To check whether a Java object is an instance of another:

```php
<?php
$string = $ba->java('java.lang.String', 'Hello');

$true  = $ba->isInstanceOf($string, 'java.lang.String');
$true  = $ba->isInstanceOf($string, 'java.lang.Object');
$false = $ba->isInstanceOf($string, 'java.util.HashMap');

// With JavaClass
$system = $ba->javaClass('java.lang.System');
$false = $ba->isInstanceOf($string, $system);

```

## Performance related

### Values method

Iterating over Java *arrays* (HashMap, ArrayList, Vector...) to retrieves 
their values in a PHP loop (while, foreach...) produce a lot of roundtrips 
with the bridge that can lead to poor performance.

Instead, you can use the `values()` method to retrieve the values in one run: 


#### Vector example

```php
<?php

$array = array_fill(0, 1000, 'Hello');
$vector = $ba->java('java.util.Vector', $array);

$values = $ba->values($vector);
// $values === $array 

```

#### HashMap example


```php
<?php
$arrOfArray = [
    'real' => true,
    'what' => 'Too early to know',
    'count' => 2017,
    'arr10000' => array_fill(0, 10000, 'Hello world')

];

$hashMap = $ba->java('java.util.HashMap', $arrOfArray);
$arrFromJava = $ba->values($hashMap);

// $arrOfArray is identical from $arrFromJava (one roundtrip) 
```




## Driver operations

!!! note
    Advanced operations are handled though the `DriverInterface` object, you can
    retrieve the Driver from the Adapter:
    
    ```php
    <?php
    $driver = $this->adapter->getDriver();
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
  

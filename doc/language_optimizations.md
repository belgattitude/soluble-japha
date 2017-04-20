# Optimization techniques

!!! note
    Be sure to have read the [how it works](./bridge_how_it_works.md) and [benchmark](./bridge_benchmarks.md) pages
    to understand the reasons behind the proposed optimization techniques. 


## Values method

Iterating over Java *arrays* (HashMap, ArrayList, Vector...) to retrieves 
their values in a PHP loop (while, foreach...) produce a lot of roundtrips 
with the bridge that can lead to poor performance.

Instead, you can use the `values()` method to retrieve the values in one run: 

### HashMap example


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

### Vector example

```php
<?php

$array = array_fill(0, 1000, 'Hello');
$vector = $ba->java('java.util.Vector', $array);

$values = $ba->values($vector);
// $values === $array 

```

## Optimizing loops

One of many techniques to solve loop/iterations issues (increase rountrips) is to build
an ArrayList, Linked list on the Java side instead of iterating from the PHP side.    

WIP: see the [JDBCPerformanceTest](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php).


## Java serialization

Whenever you need to retrieve a complex object structure (deep nesting...), you can use
object serialization on the backend. See the recipes for [json serialization](./language_recipes.md#json) as an example.

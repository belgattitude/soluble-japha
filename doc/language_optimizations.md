# Optimization techniques

!!! note
    Be sure to have read the [how it works](./bridge_how_it_works.md) and [performance](./bridge_benchmarks.md) pages
    to understand the reasons behind the proposed optimization techniques. 


## Values method

You can use the `$ba->getDriver()->value()` to quickly get PHP normalized values from a Java object. (one roundtrip).

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

## Optimizing loops

One of many techniques to solve loop/iterations issues (increase rountrips) is to build
an ArrayList, Linked list on the Java side instead of iterating from the PHP side.    

WIP: see the [JDBCPerformanceTest](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php).



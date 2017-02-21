# Benchmarks
     
!!! warning
    The following benchmarks does not intend to prove anything but might help understand
    the possible overheads when using the bridge. They were designed to illustrate the
    cost of creating objects and calling methods (roundtrips).   

## Simple benchmark

Machine: Laptop i7-6700HQ 2.60GHz, Tomcat8, japha 1.0.0, OracleJDK8, Xenial, php7.0-fpm. 
Test script: [simple_benchmark.php](https://github.com/belgattitude/soluble-japha/blob/master/test/bench/simple_benchmarks.php). 
Connection time: `$ba = new BridgeAdapter([])` varies between around 2ms (php7.0-fpm) and 5ms (php7.0-cli)

| Benchmark name |  x1 | x100 | x1000 | x10000 | Average | Memory |
|----| ----:|----:|----:|----:|-------:|----:| 
| New java(`java.lang.String`, "One") | 0.10ms| 4.28ms| 36.10ms| 286.22ms|0.0294ms|12.37Kb|
| New java(`java.math.BigInteger`, 1) | 0.24ms| 7.37ms| 38.50ms| 309.74ms|0.0321ms|12.29Kb|
| Method call `java.lang.String->length()` | 0.05ms| 2.37ms| 22.68ms| 219.08ms|0.0220ms|0.34Kb|
| Method call `java.lang.String->concat("hello")` | 0.09ms| 2.90ms| 28.60ms| 284.81ms|0.0285ms|2.09Kb|
| $a = `...String->concat('hello')` . ' world' | 0.11ms| 6.23ms| 58.94ms| 572.52ms|0.0575ms|0.42Kb|
| New java(`java.util.HashMap`, $arr) | 0.14ms| 4.04ms| 42.04ms| 407.97ms|0.0409ms|67.12Kb|
| Method call `HashMap->get('arrKey')` | 0.06ms| 2.49ms| 29.97ms| 299.10ms|0.0299ms|0.33Kb|
| Call `(string) HashMap->get('arrKey')[0]` | 0.12ms| 8.94ms| 87.57ms| 831.70ms|0.0836ms|0.34Kb|
| New `java(HashMap(array_fill(0, 100, true)))` | 0.23ms| 15.50ms| 134.13ms| 1,238.97ms|0.1251ms|1.48Kb|
| Pure PHP: call PHP strlen() method | 0.00ms| 0.00ms| 0.01ms| 0.08ms|0.0000ms|0.37Kb|
| Pure PHP: concat '$string . "hello"'  | 0.00ms| 0.00ms| 0.02ms| 0.22ms|0.0000ms|120.37Kb|

!!! note    
    Memory and average time are computed on the 11101 iterations (x1, x100...). Memory does not include the JVM side,
    that explains differences from pure php tests and Java one.      
    
The figures above will vary between systems, but intuitively you might get a glimpse about how
the bridge is sensitive to the number of object creations and method calls (roundtrips): 

> (connection time) + (number of created objects) + (number of methods) + (eventual result parsing).

Imagine a quite complex case with 100 objects instantiations and 100 method calls (from the PHP side):
 
> 2ms (connection) + 7.37ms (100 new objects) + 2.90ms (100 concat methods) = +/- 12ms minimal overhead (looks fine).   

Imagine a heavy case with 1000 new objects and 10000 method calls: 

> 2ms (connection) + 38.5ms (1000 new objects) + 284.81ms (10000 concat methods) = +/- 325ms overhead (looks too big).   

The second example should be avoided if performance matters, but the first one looks not
only viable but a (micro-)service would probably not do better (parsing the result
might give differences - a json_decode() vs parsing bridge response... But eventually you 
can also get the json from the bridge as well).

As an example, generating a report with Jasper will not even require more than 10 objects and
at max 100 method calls. The overhead here is clearly insignificant. 

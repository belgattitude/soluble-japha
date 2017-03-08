# Benchmarks
     
!!! warning
    The following benchmarks does not intend to prove anything but might help understand
    the possible overheads when using the bridge. They were designed to illustrate the
    cost of creating objects and calling methods (roundtrips).   
    
    Be sure to have read the [how it works](./bridge_how_it_works.md) before.

## Simple benchmark

Machine: Laptop i7-6700HQ 2.60GHz, Tomcat8, japha 1.2.0, OracleJDK8, pjb7.0.0 and php7.0-fpm on ubuntu xenial. 
Test script: [simple_benchmark.php](https://github.com/belgattitude/soluble-japha/blob/master/test/bench/simple_benchmarks.php). 
Connection time: `$ba = new BridgeAdapter([])` varies between around 2ms and 7ms 

| Benchmark name |  x1 | x100 | x1000 | x10000 | Average | Memory |
|----| ----:|----:|----:|----:|-------:|----:| 
| New java(`java.lang.String`, "One") | 0.14ms| 4.09ms| 34.17ms| 282.30ms| 0.03ms| 12.37Kb|
| New java(`java.math.BigInteger`, 1) | 0.05ms| 3.28ms| 32.51ms| 308.27ms| 0.03ms| 0.37Kb|
| Method call `java.lang.String->length()` | 0.05ms| 2.14ms| 21.60ms| 226.35ms| 0.02ms| 0.34Kb|
| Method call `String->concat("hello")` | 0.08ms| 2.59ms| 27.30ms| 287.79ms| 0.03ms| 2.09Kb|
| $a = `...String->concat('hello')` . ' world' | 0.09ms| 5.81ms| 54.64ms| 532.68ms| 0.05ms| 0.42Kb|
| New java(`java.util.HashMap`, $arr) | 0.16ms| 3.74ms| 33.78ms| 351.76ms| 0.04ms| 67.05Kb|
| Method call `HashMap->get('arrKey')` | 0.04ms| 2.71ms| 23.37ms| 267.88ms| 0.03ms| 0.39Kb|
| Call `(string) HashMap->get('arrKey')[0]` | 0.08ms| 5.98ms| 56.99ms| 566.69ms| 0.06ms| 0.37Kb|
| Iterate HashMap->get('arrKey')[0]` | 0.23ms| 13.40ms| 133.93ms| 1,252.59ms| 0.13ms| 2.52Kb|
| GetValues on `HashMap` | 0.05ms| 3.67ms| 36.22ms| 368.43ms| 0.04ms| 1.27Kb|
| New `java(HashMap(array_fill(0, 100, true)))` | 0.20ms| 12.04ms| 122.62ms| 1,202.73ms| 0.12ms| 0.63Kb|
| Pure PHP: call PHP strlen() method | 0.00ms| 0.00ms| 0.01ms| 0.07ms| 0.00ms| 0.37Kb|
| Pure PHP: concat '$string . "hello"'  | 0.00ms| 0.00ms| 0.04ms| 0.31ms| 0.00ms| 120.37Kb|
!!! note    
    Memory and average time are computed on the 11101 iterations (x1, x100...). 
    
    You'll realize that average time is always lower than x1 (or x10). There's some optimzations
    happening on subsequent calls that make very difficult to give sense to an average here. The best
    is to always refer to x1 for timing.
     
    Memory does not include the JVM side, that explains differences from pure php tests and Java one. 
         
         
    
The figures above will vary between systems, but intuitively you might get a glimpse about how
the bridge is sensitive to the number of object creations and method calls (roundtrips): 

> (connection time) + (number of created objects) + (number of methods) + (eventual result parsing).

Imagine a very simple case with 100 objects instantiations and 100 method calls (from the PHP side):
 
> 4ms (connection) + 4.09ms (100 new strings) + 5.81ms (100 concat methods) = +/- **13ms** minimal overhead **(looks fine)**.   

Imagine a bad scenario with 1.000 new objects and 10.000 method calls: 

> 4ms (connection) + 34.1ms (1000 new objects) + 532.68ms (10000 concat methods) = +/- **570ms** overhead **(looks too much)**.   

The second example should be avoided if performance matters, but the first one looks not
only viable but a (micro-)service would probably not do better (parsing the result
might give differences - a json_decode() vs parsing bridge response... But eventually you 
can also get the json from the bridge as well).

As an example, generating a report with Jasper will not even require more than 10 objects and
at max 100 method calls. The overhead here is clearly insignificant. 

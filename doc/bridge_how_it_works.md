# How it works

## The big picture

Behind the scenes, all Java object instantiations and method calls 
are forwarded to the JVM through a maintained connection tunnel 
with the JavaBridge server. 

You can think about it like a database connection on which you execute tiny queries, but
with some differences: 

The protocol used between Java and PHP is based on HTTP and serialized in XML. 
Here's what would be transmitted if you call `$ba->javaClass('myJClass')->aJMethod(2)`:
    
```xml
<c value="myJClass" p="Class"></c>
<i value="0" method="aJMethod" p="Invoke"><object value="2"/></i>
```    

In addition to this, object state is *automatically* maintained between both Java and PHP runtimes.
The PHP client keeping a proxied object representation over its counterpart on the JVM side.
 
To complete the picture, there is also some magic happening for handling types differences (casting)
and method overloading (that is not supported by PHP). 

## Consequences

Look at the [benchmarks](./bridge_benchmarks.md) where you can have a glimpse
on how the bridge is sensitive to the number of roundtrips and then to the 
possible [optimizations techniques](./language_optimizations.md) 
to reduce the number of roundtrips. 



                                                                


 
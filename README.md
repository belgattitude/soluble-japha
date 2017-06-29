# soluble-japha  

[![PHP Version](http://img.shields.io/badge/php-5.5+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![PHP Version](http://img.shields.io/badge/php-7.1+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![codecov](https://codecov.io/gh/belgattitude/soluble-japha/branch/master/graph/badge.svg)](https://codecov.io/gh/belgattitude/soluble-japha)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)
[![HHVM Status](https://php-eye.com/badge/soluble/japha/hhvm.svg)](https://php-eye.com/package/soluble/japha)

In short **soluble-japha** allows to write Java code in PHP and interact with the JVM ecosystem.  

----

> Read the [http://docs.soluble.io/soluble-japha](http://docs.soluble.io/soluble-japha) 
> website for complete information

----

As meaningless examples:

```php
<?php
// Some standard JVM classes
$hashMap = $ba->java('java.util.HashMap', [         
        'message' => 'Hello world',                 
        'value'   => $ba->java('java.math.BigInteger', PHP_INT_MAX)
]);
$hashMap->put('message', '你好，世界');
echo $hashMap->get('message');
```

-------

```php
<?php
// An imaginary java library class (i.e. NLP, Android, Jasper, Tensorflow,
// enterprise stuff, esoteric java lib/driver or your own Java class...)
try {    
    $javaLib = $ba->java('an.imaginary.JavaLibraryClass', 'param1', 'param2');

    $results = $javaLib->aMethodOnJavaLibExecutedOnTheJVM(
                            // Method parameters                             
                            $hashMap->get('message'),
                            $ba->java('java.io.BufferedReader',
                                 $ba->java('java.io.FileReader', __FILE__)
                            ),
                            $ba->javaClass('java.util.TimeZone')->SHORT
                        );
    
    foreach ($results as $key => $values) {    
        echo "$key: " . $values[0] . PHP_EOL;            
    }    
} catch (Exception\ClassNotFoundException $e) { 
    echo $e->getMessage(); 
} catch (Exception\JavaException $e) { 
    echo $e->getMessage() . ' [' . $e->getJavaClassName() . ']';
    echo $e->getStackTrace(); 
}

```

## Use cases 

**Expand the PHP horizons to the Java ecosystem**, especially whenever you want 
to take advantage of

- some compelling libraries *([Jasperreports](http://community.jaspersoft.com/project/jasperreports-library), [CoreNLP](http://stanfordnlp.github.io/CoreNLP/), [FlyingSaucer](https://github.com/flyingsaucerproject/flyingsaucer/releases), [Jsoup](https://jsoup.org/)...)*
- benefit from JVM performances *([Deeplearning4J](https://deeplearning4j.org/)...)* or wrappers *([TensorFlow Java Api](https://www.tensorflow.org/api_docs/)...)*.
- when a pure-PHP alternative does not exists *(Android, driver, closed api, enterprise...)* 
- or simply for the fun of it.  

> The freedom allowed by `soluble-japha` is not fit for every scenarios. 
> Be sure to read the [considerations](#considerations) and [performance](#performance) 
> sections to learn more. 

## Features

`soluble-japha` provides a PHP client to interact with the Java Virtual Machine.       

- [x] Write Java code from PHP *(in a similar way from equivalent java code)*.  
- [x] Keep *programmatic* code control from the PHP side *([function oriented vs REST](#considerations))*.
- [x] Java execution on the JVM ensuring compatibility and efficiency *(proxied objects)*.
- [x] No need to write a service layer prior to usage *(the Java object is the contract)*.
- [x] Fast network based communication between runtimes, no JVM startup effort.
- [x] Solid foundation to create, develop *or publish* PHP wrappers over java libs.
      

> For user of previous versions, **soluble-japha** client replaces the original/legacy [PHPJavaBridge](http://php-java-bridge.sourceforge.net/pjb/) 
> `Java.inc` implementation and has been completely refactored to fit modern practices 
> and PHP7. 
> See the [differences here](./doc/notes_legacy.md) and the [legacy compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat) if needed.

## Requirements

- Version 2.0 -> PHP 7.1 [![PHP Version](http://img.shields.io/badge/php-7.1+-ff69b4.svg)]
- PHP 5.6, 7.0+, 7.1+ or HHVM >= 3.9 *(for PHP5.5 use the "^0.13.0" releases)*.

## Documentation

 - Read the [Manual](http://docs.soluble.io/soluble-japha/)  
 - and alternatively the [API documentation](http://docs.soluble.io/soluble-japha/api/) available.

## Installation

Installation in your PHP project **(client)**
 
```console
$ composer require soluble/japha
```

## Considerations

> In short, **the bridge shines** whenever you need to use directly a Java library
> within a reasonable number of method calls. Otherwise implement 
> **REST or RPC approaches** for first-class system integrations.

The soluble-japha bridge can be seen as a `function oriented` solution in 
comparison to `resource oriented` ones *(i.e. REST,...)*. From REST or even 
RPC-based solutions *(XMLRPC, JsonRPC or [gRPC](https://github.com/grpc/grpc))*, 
the bridge skips the need to write a service layer on 
the Java side and allows a more *programmatic* approach to PHP developers.

Depending on usage, the benefits of freedom offered by the bridge
can become a limitation in term of performance. Keep in mind that 
the bridge is sensitive to the number of objects and method calls
(named `roundtrips`) and if few hundreds of methods calls are 
often insignificant (a `roundtrip` is generally less than 0.1ms) going further
its target scenarios can be disappointing. In those case, 
traditional approaches like REST should be considered and applied instead.     
  
That said, the bridge is a good, reliable and sometimes preferable alternative 
over REST for scenarios where a reasonable number of methods calls is intended.
 
Be sure to read the 
- [http://docs.soluble.io/soluble-japha/bridge_how_it_works/](http://docs.soluble.io/soluble-japha/bridge_how_it_works/)
- [http://docs.soluble.io/soluble-japha/bridge_benchmarks/](http://docs.soluble.io/soluble-japha/bridge_benchmarks/)  

## Support

Please fill any issues on the [offical tracker](https://github.com/belgattitude/soluble-japha/issues). 
If you like to contribute, see the [contribution guidelines](https://github.com/belgattitude/soluble-japha/blob/master/CONTRIBUTING.md). 
All P/R are warmly welcomed. 
                         
   
## Future ideas

Short term

- [ ] Achieve at least 80% of unit testing for legacy code.
- [ ] Remove obsolete code from PJB62 driver (will also increase coverage)
- [ ] Work on performance

Experiments

- [ ] Improve proxy and use of [ProxyManager](https://github.com/Ocramius/ProxyManager)
- [ ] Drop XML protocol in favour of protocol buffers or [GRPC](http://www.grpc.io/) 
- [ ] Create a JSR-223 php extension in Go, like this [experiment](https://github.com/do-aki/gophp_sample)

### Credits

* This code is principally developed and maintained by [Sébastien Vanvelthem](https://github.com/belgattitude).
* Special thanks to [all of these awesome contributors](https://github.com/belgattitude/soluble-japha/network/members)
* This project is based on the Java.inc work made by the [PHPJavaBridge developers](http://php-java-bridge.sourceforge.net/pjb/contact.php#code_contrib). 
  
## Coding standards and interop

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)


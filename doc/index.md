# PHP/Java integration <small>where PHP meets Java again</small>

[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![PHP Version](https://img.shields.io/badge/php-5.5+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![PHP Version](https://img.shields.io/badge/php-7.1+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![PHP Version](https://img.shields.io/badge/php-8.0+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![codecov](https://codecov.io/gh/belgattitude/soluble-japha/branch/master/graph/badge.svg)](https://codecov.io/gh/belgattitude/soluble-japha)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)

## Introduction

In short **soluble-japha** allows to write Java code in PHP and interact with the JVM ecosystem.
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

**Expand the PHP horizons to the Java ecosystem** whenever you want
to quickly play with

- some compelling libraries *([Jasperreports](http://community.jaspersoft.com/project/jasperreports-library), [CoreNLP](http://stanfordnlp.github.io/CoreNLP/), [FlyingSaucer](https://github.com/flyingsaucerproject/flyingsaucer/releases), [Jsoup](https://jsoup.org/)...)*
- benefit from JVM performances *([Deeplearning4J](https://deeplearning4j.org/)...)* or wrappers *([TensorFlowApi](https://www.tensorflow.org/api_docs)*...)
- when a pure-PHP alternative does not exists *(Android, driver, closed api, enterprise...)*
- or simply for the fun of it.


## Features

`soluble-japha` provides a PHP client to interact with the Java Virtual Machine.

- [x] Write Java code from PHP *(in a similar way from equivalent java code)*.
- [x] Keep *programmatic* code control from the PHP side *([function oriented vs REST](#considerations))*.
- [x] Java execution on the JVM ensuring compatibility and efficiency *(proxied objects)*.
- [x] No need to write a service layer prior to usage (**the Java object is the contract**).
- [x] Network based communication between runtimes (**no JVM startup effort**).
- [x] Solid foundation to create, develop *or publish* PHP wrappers over java libs.

## How it works


```mermaid
sequenceDiagram
    Note over SolubleJapha: Serialize call
    SolubleJapha->>+PHPJavaBridgeServer: XML Payload
    Note over PHPJavaBridgeServer, JVM: Deserialize and init context
    PHPJavaBridgeServer->JVM: Execute <br />the call on the JVM
    PHPJavaBridgeServer-->>-SolubleJapha: XML Payload
    Note over SolubleJapha: Deserialize response
```

## Considerations

!!! summary

    In short, **the bridge shines** whenever you need to use directly a Java library
    within a reasonable number of method calls. Otherwise implement
    **REST or RPC approaches** for first-class system integrations. See the
    [how it works](./bridge_how_it_works.md) and [performance](./bridge_benchmarks.md)
    sections to learn more.

The soluble-japha bridge can be seen as a `function oriented` solution in
comparison to `resource oriented` ones *(i.e. REST,...)*. From REST or even
RPC-based solutions *(XMLRPC, JsonRPC or [gRPC](https://github.com/grpc/grpc))*,
the bridge skips the need to write a service layer on
the Java side and allows a more *programmatic* approach to PHP developers.

Depending on usage, the benefits of freedom offered by the bridge
can become a limitation in term of performance. Keep in mind that
the bridge is sensitive to the number of objects and method calls
(named `roundtrips`) and if few hundreds of methods calls are
often insignificant (a `roundtrip` is generally less than 0.1ms,
see [benchmarks](./bridge_benchmarks.md)) going further
its target scenarios can be disappointing. In those case,
traditional approaches like REST should be considered and applied instead.

That said, the bridge is a good, reliable and sometimes preferable alternative
over REST for scenarios where a reasonable number of methods calls is intended
or whenever you want to keep control of the code on the PHP side.

!!! tip
    Be sure to read the [optimizations](./language_optimizations.md) techniques while developing with the bridge.

## Support

Please fill any issues on the [offical tracker](https://github.com/belgattitude/soluble-japha/issues).
If you like to contribute, see the [contribution guidelines](https://github.com/belgattitude/soluble-japha/blob/master/CONTRIBUTING.md).
All P/R are warmly welcomed.


## License

Published under the [MIT License](https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md). Copyright (c) 2013-2020 Vanvelthem Sébastien



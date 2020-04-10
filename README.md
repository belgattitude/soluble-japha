# soluble-japha

[![PHP Version](https://img.shields.io/badge/php-5.5+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![PHP Version](https://img.shields.io/badge/php-7.1+-ff69b4.svg)](https://packagist.org/packages/soluble/japha)
[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![codecov](https://codecov.io/gh/belgattitude/soluble-japha/branch/master/graph/badge.svg)](https://codecov.io/gh/belgattitude/soluble-japha)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![Total Downloads](https://poser.pugx.org/soluble/japha/downloads.png)](https://packagist.org/packages/soluble/japha)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)

In short **soluble-japha** allows to write Java code in PHP and interact with the JVM ecosystem.

----

> Read the doc on [https://belgattitude.github.io/soluble-japha](https://belgattitude.github.io/soluble-japha)
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

use Soluble\Japha\Bridge\Exception;

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

And if you're wondering what's the `$ba` object, it's a connection
to the java bridge server:

```php
<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use Soluble\Japha\Bridge\Exception as BridgeException;

$options = [
    'servlet_address' => 'localhost:8080/MyJavaBridge/servlet.phpjavabridge'
];

try {
    $ba = new BridgeAdapter($options);
} catch (BridgeException\ConnectionException $e) {
    // Server is not reachable
    echo $e->getMessage();
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

- Version `^2.0` requires PHP 7.1 ![PHP Version](http://img.shields.io/badge/php-7.1+-ff69b4.svg)

> **Important**. There's **NO API BC-BREAK** between v0.13, v1.x and v2.x so you should be
> able to upgrade safely between releases. The choice to increment version numbers to drop
> support for older php versions was made to avoid any confusion with multiple php installs.



If you're looking for compatibility with older PHP versions, note that:

- Version `^1.0` requires PHP 5.6 ![PHP Version](http://img.shields.io/badge/php-5.6+-ff69b4.svg) and works with HHVM.
- Version `^0.13` requires PHP 5.5 ![PHP Version](http://img.shields.io/badge/php-5.5+-ff69b4.svg) and works with HHVM.


## Documentation

 - Go to https://belgattitude.github.io/soluble-japha

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


### Credits

* This code is principally developed and maintained by [Sébastien Vanvelthem](https://github.com/belgattitude).
* Special thanks to [all of these awesome contributors](https://github.com/belgattitude/soluble-japha/network/members)
* This project is based on the Java.inc work made by the [PHPJavaBridge developers](http://php-java-bridge.sourceforge.net/pjb/contact.php#code_contrib).

### Special mention

Grateful thanks to JetBrains for granting an opensource license of PHPStorm and Idea. Really recommend !!!

[![PHPStorm](./doc/images/phpstorm.svg)](https://www.jetbrains.com)

## Coding standards and interop

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)


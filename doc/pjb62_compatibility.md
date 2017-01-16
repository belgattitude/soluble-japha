# PHP/Java bridge legacy compatibility 

## Introduction

Historically the [PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) client implementation didn't
support namespaces. If you have existing code relying on previous implementations and don't want to refactor, 
you can install a [compatibility layer](https://github.com/belgattitude/soluble-japha-pjb62-compat).
 
## Installation

Simply add the `soluble/japha` compatibility layer to your [composer](http://getcomposer.org/) dependencies :

```console
$ composer require "soluble/japha-pjb62-compat"
```

## Documentation

See the [official repo](https://github.com/belgattitude/soluble-japha-pjb62-compat).
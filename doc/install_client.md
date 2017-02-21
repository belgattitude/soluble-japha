# PHP installation

## Quick install

The `soluble-japha` client supports [composer](http://getcomposer.org/). In your project directory, simply type:

```bash
$ composer require soluble/japha
```

*Once done, jump to the [server install](./install_server.md) step or
read the notes below for more information about composer support and
requirements.* 

-------

## Requirements
 
- PHP 5.5+, 7.0+ or HHVM 3.2
- PHP xml extensions enabled (domxml)

## Composer notes

If you're not familiar with composer you can jump
to the official [install docs](https://getcomposer.org/doc/00-intro.md),
ensure `soluble-japha:^1.0` is present in your project `composer.json` file and
run the `composer update` command.

Most modern frameworks relies on composer out of the box, if not the case 
ensure the following file is included in your bootstrap file *(index.php, ...)*:

```php
<?php
// include the composer autoloader
require 'vendor/autoload.php';
```






 
 







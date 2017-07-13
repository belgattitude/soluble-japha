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

### Versions

- For PHP7.1, use the ^2.0.0 version.
- For PHP5.6, use the ^1.0.0 version.
- For PHP5.5, use the ^0.13.0 version.

!!! note
    While the `^2.0.0` version is PHP7.1 only, there were no bc-break with the `1.4.0`.


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






 
 







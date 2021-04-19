# PHP installation


=== "PHP7.4 & PHP8.0"

    ```bash
    $ composer require soluble/japha
    ```


=== "PHP7.2"

    ```bash
    $ composer require soluble/japha@2.8.0
    ```

=== "PHP7.1"

    ```bash
    $ composer require "soluble/japha@^2.7.0"
    ```

=== "PHP 5.6"
    *For PHP5.6, use the ^1.0.0 version, BC compat with ^2.0.0.*

    ```bash
    $ composer require "soluble/japha@^1.4.0"
    ```

=== "PHP 5.5"
    *For PHP5.5, use the ^0.13.0 version.*

    ```bash
    $ composer require "soluble/japha@^0.13.0"
    ```

----


!!! tip
    Once done, jump to the [server install](./install_server.md) step.

-------


## Notes

If you're not familiar with composer you can jump
to the official [install docs](https://getcomposer.org/doc/00-intro.md),
ensure `soluble-japha:^2.0` (or `soluble-japha:^1.0`) is present in your project `composer.json` file and
run the `composer update` command.

Most modern frameworks relies on composer out of the box, if not the case
ensure the following file is included in your bootstrap file *(index.php, ...)*:

```php
<?php
// include the composer autoloader
require 'vendor/autoload.php';
```















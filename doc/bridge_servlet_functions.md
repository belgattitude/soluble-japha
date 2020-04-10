# Servlet functions

It's possible to call a specific java servlet function with the `$ba->getDriver()->invoke()`
method. Just ensure the first parameter is null.

## Example

```php

<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
                           'driver' => 'Pjb62',
                           'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
                        ]);

// To get the servlet options in use.
$options = $ba->getDriver()->invoke(null, 'getOptions');

// To set file_encoding
$encoding = $ba->getDriver()->invoke(null, 'setFileEncoding', ['ASCII']);

```

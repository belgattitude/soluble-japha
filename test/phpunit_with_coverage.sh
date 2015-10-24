#!/bin/sh
php -d zend_extension=xdebug.so  ~/.composer/vendor/bin/phpunit

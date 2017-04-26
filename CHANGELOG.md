# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.4.1 (2017-04-26)

## Added

- Created `Soluble\Japha\Bridge\Exception\BrokenConnectionException` to mask
  internal pjb62 drive BrokenConnectionException. Documented in DriverInterface as well.  

## Improved

- PHPDoc, improved documentation on Exception in DriveInterface and AbstractDriver.

- PHPDoc, magic methods `__call()`, `__get()` and `__set()` nows inform
  about possible exception. Thanks @marcospassos for persisting on this.
  

## 1.4.0 (2017-04-25)

## Added

- `Interfaces\JavaObject` exposes magic methods `__call()`, `__get()` and `__set()` 
   to better reflect that method calls and property accesses 
   will be delegated to the remote Java object.
   
## Changed
  
- Possible bc-break in undocumented feature. The `DB\DriverManager::getJdbcDSN()` has changed its signature
  and starts now with the driver name. An `$options` array allows to pass extra options.
    
## Improved
    
- Serialization with `__sleep()` and `__wakeup()` has preliminary test support    

## 1.3.1 (2017-04-23)

This release has been tested with the latest official phpjavabridge 7.1.3.

## Added

- Pjb62\Driver added method `setExitCode(int $code)` from 7.1.3 upstream merge, 
  requires PHP 7.1.3+ to be interpreted on the bridge side.

## 1.3.0 (2017-04-23)

## Added

- `Interfaces\JavaObject` now implements `ArrayAccess` 
  the following code is possible without calling java methods:
  
  ```php
  $hashMap = $ba->java('java.util.HashMap');
  $hashMap['key'] = 'cool';
  if (isset($hashMap['key']) {
     echo $hashMap['key']; 
     unset $hashMap['key'];
  }
  ``` 
   
- Added convenience exception: `NoSuchFieldException`   

## Changed

- Possible bc-break in undocumented `Adapter` option: `java_default_timezone`. 
  It won't fall back to php default timezone if null. This feature is subject to caution.
- Removed last `func_get_args` uses, replaced by PHP5.6 variadic notation in `AbstractJava` and `JavaException`.
- `Interfaces\JavaObject` now implements `IteratorAggregate`, this behaviour was 
  already working but not *statically* stated.
      
## Documentation

- Setting the default java timezone with `TimeZone.setDefault()` should be avoided
  as its value is global on the JVM.

## 1.2.2 (2017-05-20)

## Improved

- Improved messages on socket errors (errno and errstr)
- Added 'protected' members on relevant methods and attributes. (WIP)
- Improved PHPDoc comments with types (WIP)
- Added additional checks for errors in Client.php.

### Fixed

- Unit tests: Fixed minor issue with mariadb connector in JDBC tests (serverTimezone)

### Added

- Unit tests for inner classes and enumset.

### Removed

- Removed Client::createParserString and moved the code into SimpleParser *(only used by HHVM)*.
  If you rely by mistake on this method, it can be replaced by `new ParserString()`, not considered
  as a bc-break because this method is only used by hhmv xml parser and was not documented.

### Documentation

- Added documentation for inner class support.
- Added recipes for gson, json-io serialization. 


## 1.2.1 (2017-03-21)

### Fixed

- Minor fix, wrong message referring to legacy `java_inspect` and `java_values`. 

## 1.2.0 (2017-03-06)

### Added

- New driver methods: `setFileEncoding()` and `getConnectionOptions()` currently internal use only. 
- Support for php-java-bridge 7.0.0.
- Possibility to call servlet methods from the driver `$ba->getDriver()->invoke(null, 'myMethod', [$params])`.

### Improved

- Set utf-8 by default for html_special_chars (no need to set in php.ini) 
- Replaces array_push($arr, $val) by $arr[] = $val for little perf improvement in Driver 
- Removed devDependency on devster/ubench for simple benchmarks.

### Fixed

- Minor: JavaClass::getName() now return string instead og JavaObject(java.lang.String).


## 1.1.1 (2017-03-02)

### Fixes

- Minor bugfixes with java_lifetime and java_truncate thanks to phpstan.

## 1.1.0 (2017-03-01)

### Improved

- Improving exception through JavaExceptionInterface, see [#35](https://github.com/belgattitude/soluble-japha/issues/35)

### Added

- Added `Adapter::values()` method, see [#34](https://github.com/belgattitude/soluble-japha/issues/34)


## 1.0.1 (2017-02-21)

### Fixed

- Minor bug in JDBC unit tests when mysql-connector is >= 6.0 

### Added 

- Huge documentation update

## 1.0.0 (2017-02-17)

### Added 

- `DriverInterface::values()` method added.
- Doc: considerations and best practices
- More JDBC tests some optimizations examples with `DriverInterface::values()`


## 1.0.0 (2017-02-12)

### Added 

- `DriverInterface::values()` method added.
- Doc: considerations and best practices
- More JDBC tests some optimizations examples with `DriverInterface::values()`


## 0.14.4 (2017-02-09) 

### Added

- Documentation for context/sessions/...
- Compatibility with soluble php-java-bridge 6.2.11-rc-1
- Credits updated for the refactored Pjb62 client driver


## 0.14.3 (2017-02-02) 

### Added

- Minor improvement for timezone interop (code cleanup).


## 0.14.2 (2017-01-30) 

### Added

- Unit tests handles phpjavabridge 6.2.11-dev.

## 0.14.1 (2017-01-15)
   
### Improved

- Better detection of broken connections `BrokenConnectionException`
- Improved tests suite for tomcat 

## 0.14.0 (2017-01-14)

### Breaking change

- From version 0.14+, minimum supported version is php 5.6.
  Support for PHP 5.5 has been dropped, due to usage of
  variadic functions. The API is still the same, and if you
  require PHP5.5 support, please install the v0.13.0 instead.
  
  A branch have been saved for [pre-5.6](https://github.com/belgattitude/soluble-japha/tree/pre5.6_0.13.0) support. 
  It can be used to provide 0.13.x patches for php5.5.
   
### Improved

- Better logging support and error reporting [see #22](https://github.com/belgattitude/soluble-japha/issues/22).
- Support for variadic notation [see #16](https://github.com/belgattitude/soluble-japha/issues/16).
- A lot a minor fixes (types, phpdoc...)


## 0.13.0 (2016-12-01)

### BREAKING CHANGES

- Renamed `$driver->getJavaContext()` into `$driver->getContext()`.

### Added

- Support for `$adapter->getDriver()->getContext()`.
- Support for `$adapter->getDriver()->getJavaSession()`.


## 0.12.0 (2016-11-28)

### Added

- Support for `getClassName()` in Adapter, Close [#30](https://github.com/belgattitude/soluble-japha/issues/30)
- Support for `invoke()` method in Driver. Close [28](https://github.com/belgattitude/soluble-japha/issues/28)
- Support for `getClassName()` and `inspect()` methods in DriverInterface. Close [#29](https://github.com/belgattitude/soluble-japha/issues/29)
- Added `JavaException::getJavaClassName()` method to improve exception handling. Close [31](https://github.com/belgattitude/soluble-japha/issues/31)  
- Support for `getJavaContext()` in Driver

### Changed

- Update pjbserver-tools devDependencies to ^2.1.0
- [Unit tests] JDBC tests are now disabled by default in `phpunit.xml.dist`.
- [Unit tests] Improved test suite, larger tests files have been splitted.   

## 0.11.9 (2016-10-13)

### Added

- Minor documentation changes.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#24](https://github.com/belgattitude/soluble-japha/pull/24) fix an old issue
  preventing 'array' cookies to be correctly forwarded to java bridge. Thanks
  [Diego Sainz](https://github.com/diegosainz) for the fix. 


## 0.11.8 (2016-09-14)

- Intial support for PSR-3 logger interface
- Support for pjbserver-tools updated to ^2.0.4.
- Minor fixes in documentation

## 0.11.7 (2016-07-08)

- Improved documentation
- Remove server autostart (obsolete code)
- Added `\Soluble\Japha\Bridge\Exception\ConnectionException`
- Dropped support for PHP5.5 (should

## 0.11.5 (2016-07-04)

- Support for pjbserver-tools updated to ^2.0.3.
- Updated documentation

## 0.11.5 (2016-07-03)

- Support for pjbserver-tools updated to ^2.0.0.

## 0.11.4 (2016-07-02)

- Support for pjbserver-tools updated to ^1.1.0.

## 0.11.3 (2016-06-11)

- [Bug] Fix issue #17, [constructor overloading](https://github.com/belgattitude/soluble-japha/issues/17)

## 0.11.2 (2016-06-11)

- [Cleanup] Removed obsolete connection error message

## 0.11.1 (2016-06-11)

- [Cleanup] Cleanup some minor issues

## 0.11.0 (2016-06-11)

- [BC-Break] Split legacy pjb62 compatibility layer in a separate repo
  If you still require the legacy compatibility mode you need to add
  the 'soluble\japha-pjb62-compat' to your composer deps 
- [Enhancement] Removed all global constants
- [Enhancement] Fixed some scrutinizer issues
- [Enhancement] Removed obsolete autoloader code


## 0.10.0 (2016-05-13)

- Drop PHP 5.3 support (use < 0.10.0 if 5.3 is required)
  
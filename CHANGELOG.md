# CHANGELOG

## 0.14.2 (2017-xx-xx) - NOT RELEASED

### Improved

- General doc improvements and reference to [pjb-starter-gradle](https://github.com/belgattitude/pjb-starter-gradle/).


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
  
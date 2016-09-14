# CHANGELOG

### 0.11.8 (2016-09-14)

- Support for PSR-3 logger interface
- Support for pjbserver-tools updated to ^2.0.4.
- Minor fixes in documentation

### 0.11.7 (2016-07-08)

- Improved documentation
- Remove server autostart (obsolete code)
- Added `\Soluble\Japha\Bridge\Exception\ConnectionException`
- Dropped support for PHP5.5 (should

### 0.11.5 (2016-07-04)

- Support for pjbserver-tools updated to ^2.0.3.
- Updated documentation

### 0.11.5 (2016-07-03)

- Support for pjbserver-tools updated to ^2.0.0.

### 0.11.4 (2016-07-02)

- Support for pjbserver-tools updated to ^1.1.0.

### 0.11.3 (2016-06-11)

- [Bug] Fix issue #17, [constructor overloading](https://github.com/belgattitude/soluble-japha/issues/17)

### 0.11.2 (2016-06-11)

- [Cleanup] Removed obsolete connection error message

### 0.11.1 (2016-06-11)

- [Cleanup] Cleanup some minor issues

### 0.11.0 (2016-06-11)

- [BC-Break] Split legacy pjb62 compatibility layer in a separate repo
  If you still require the legacy compatibility mode you need to add
  the 'soluble\japha-pjb62-compat' to your composer deps 
- [Enhancement] Removed all global constants
- [Enhancement] Fixed some scrutinizer issues
- [Enhancement] Removed obsolete autoloader code


### 0.10.0 (2016-05-13)

- Drop PHP 5.3 support (use < 0.10.0 if 5.3 is required)
  
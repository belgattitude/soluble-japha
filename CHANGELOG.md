# CHANGELOG

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
  
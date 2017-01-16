## Installation

In order to install **soluble/japha** you need both a **client** and a **server**.

### Prerequisites

- PHP 5.5+, 7.0+ or HHVM >= 3.2.
- Installed [JRE or JDK 7+](./server/install_java.md)

### Client installation

You can install **soluble-japha** through [composer](http://getcomposer.org/), 
simply type : 

```bash
$ composer require soluble/japha
```

Most modern frameworks relies on composer out of the box, if not the case ensure the following file is included in your bootstrap file *(index.php, ...)*:

```php
<?php
// include the composer autoloader
require 'vendor/autoload.php';
```

### Server installation

The PHP-Java communication is handled by a intermediate server (the bridge) running 
on your local machine (or network) on which the **soluble/japha** client can connect. 

Depending on your use case, the recommended production setup is to deploy the bridge as a servlet on a J2EE server. 

But as this kind of setup can be tedious for development, unit tests... or simply not needed, 
you can start in minutes by setting up the [**standalone server**](https://github.com/belgattitude/pjbserver-tools) instead.
*Of course if your looking for best performance, scalability, security... you can make the J2EE install later on.*  
 
#### Option 1: Standalone bridge server (quick)

Clone the [pjbserver-tools](https://github.com/belgattitude/pjbserver-tools) repository in a custom directory an run [composer](http://getcomposer.org) update command.
   
```bash
$ mkdir -p /my/path/pjbserver-tools
$ cd /my/path/pjbserver-tools
$ git clone https://github.com/belgattitude/pjbserver-tools.git .
$ composer update   
$ ./bin/pjbserver-tools pjbserver:start -vvv ./config/pjbserver.config.php.dist
```

The server will start on default port ***8089***. If you would like to change it, create a local copy of `./config/pjbserver.config.php.dist`
and use that in the above command.
   
Use the commands `pjbserver:stop`, `pjbserver:restart`, `pjbserver:status` to control or query the server status.

For more information about the standalone server, have a look to the [pjbserver-tools repo](https://github.com/belgattitude/pjbserver-tools). 

#### Option 2: Bridge server on J2EE (longer) 

Depending on your usage, deploying the bridge server onto a J2EE server like Tomcat offers better performance, 
scalability and security. 
  
The documentation is still in progress but you can have a look to the [J2EE server install](./install_server_j2ee.html)

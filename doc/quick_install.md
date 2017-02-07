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

> **WARNING** The phpjavabridge server is not supposed to be run on a public facing server
> and its use should be limited to interactions on the same host/network with the php client.
> Do not run it as root neither as it exposes the JVM methods through the network. 

Two options exists, the first one is using the [**standalone server**](https://github.com/belgattitude/pjbserver-tools) which
helps to start a server with little Java knowledge. Its recommended use is for development, unit tests...
 
For **production prefer the Option 2**, you can also start with the standalone and switch to second option later on. 
 
#### Option 1: Standalone bridge server (easy to start in cli - development)

   Clone the [pjbserver-tools](https://github.com/belgattitude/pjbserver-tools) repository in a custom directory an run [composer](http://getcomposer.org) update command.
   
   To get **a quick glimpse** use the [pjbserver-tools standalone server](https://github.com/belgattitude/pjbserver-tools).
   
   ```console
   $ git clone https://github.com/belgattitude/pjbserver-tools.git
   $ cd pjbserver-tools
   $ composer update   
   $ ./bin/pjbserver-tools pjbserver:start -vvv ./config/pjbserver.config.php.dist
   ```

   > The server will start on default port ***8089***. If you like to change it, create a local copy of `./config/pjbserver.config.php.dist`
   > and refer it in the above command.
   >
   > Use the commands `pjbserver:stop`, `pjbserver:restart`, `pjbserver:status` to control or query the server status.
   >
   > Read the [doc](https://github.com/belgattitude/pjbserver-tools) about the standalone server to learn how to add java libs. 

#### Option 2: Tomcat servlet (production) 

   Build you own .war file including the JavaBridge servlet with either:
        
   - An easy to setup starter: [pjb-starter-gradle](https://github.com/belgattitude/pjb-starter-gradle/)
   
   or 

   - The php-java-bridge repo: [php-java-bridge](https://github.com/belgattitude/php-java-bridge)
   
     - You can use the pre-compiled template (JavaBridgeTemplate.war) available on the [release page](https://github.com/belgattitude/php-java-bridge/releases).
       or build your own (adding dependencies and customizing the web.xml)  
   
   Once build simply deploy to tomcat webapps folder. 

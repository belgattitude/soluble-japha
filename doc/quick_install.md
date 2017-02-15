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

Multiple options exists and are documented in the [install_server.md](./install_server.md) doc but
for a quick an easy start you can cuild your own PHPJavaBridge instance with the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot)

As an example:
    
```bash
$ git clone https://github.com/belgattitude/pjb-starter-springboot
$ cd pjb-starter-springboot
$ # An example build with jasperreports and mysql jdbc connector included
$ ./gradlew build -I init-scripts/init.jasperreports.gradle -I init-scripts/init.mysql.gradle
$ # Run the PHPJavaBridge server
$ java -jar ./build/libs/JavaBridgeStandalone.jar -Dserver_port=8089   
``` 
Check the [landing page](http://localhost:8089) for status and use the connection `localhost:8089/servlet.phpjavabridge` in your bridge connection parameters.

*This example includes jasperreports and the mysql jdbc connector deps, but you can easily
customize with provided [examples scripts](https://github.com/belgattitude/pjb-starter-springboot/blob/master/init-scripts/README.md) for OpenNLP, PDFBox, POI, CoreNLP... or provide your
own customizations.* 

Deploying on [Tomcat]((./server/install_tomcat.md)) is easy as 

```bash
$ cp ./build/libs/JavaBridgeTemplate.war /var/lib/tomcat8/webapps/MyJavaBridge.war      
```  
Wait few seconds and check the [landing page](http://localhost:8080/MyJavaBridge) for status and use the connection `localhost:8080/MyJavaBridge/servlet.phpjavabridge` in your bridge connection parameters.    

*Tested on Tomcat 7 and Tomcat 8 (Win/Linux), change the webapps directory to match your installation.*
    

## Install PHP-Java-bridge server (Java)

### Requirements
 
- Installed [JRE or JDK 7,8+](./server/install_java.md).

## Introduction

To communicate with the JVM, `soluble/japha` requires a bridge server running on your local machine (or network). 

First you need to ensure you have a valid JDK installed, see the [*nix java install guide](./server/install_java.md). 
You can test if you have a JDK/JRE installed by typing :

```sh
$ java -version 
``` 

## Installation 
 
To improve developer experience you can use one of the following methods to install the bridge.

> Be aware that ***the port used for java bridge should not be public*** for security reasons.

1. Option 1: Build your own PHPJavaBridge instance with the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot)

   The most easy way is to build your own PHPJavaBridge server with the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot) 
   and customize it to include your required dependencies. As 2 minutes example:
    
   ```console
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
   
   ```
   $ cp ./build/libs/JavaBridgeTemplate.war /var/lib/tomcat8/webapps/MyJavaBridge.war      
   ```  
   Wait few seconds and check the [landing page](http://localhost:8080/MyJavaBridge) for status and use the connection `localhost:8080/MyJavaBridge/servlet.phpjavabridge` in your bridge connection parameters.    
   
   *Tested on Tomcat 7 and Tomcat 8 (Win/Linux), change the webapps directory to match your installation.*
    
   
2. Option 2: Build your own *barebone* PHPJavabridge from sources
       
   If the starter looks to heavy or you don't like the spring-boot way, you can 
   build the project from the original [PHPJavaBridge](https://github.com/belgattitude/php-java-bridge) sources.
   
   They basically do the same thing, the barebone version will save you around 11Mb of springboot deps and
   rely on a *more standard* servlet `web.xml` for its configuration but does not provide some features like
   a standalone embedded tomcat...    
   
   ```console
   $ git clone https://github.com/belgattitude/php-java-bridge.git
   $ cd php-java-bridge
   $ # An example build with jasperreports and mysql jdbc connector included
   $ ./gradlew build -I init-scripts/init.jasperreports.gradle -I init-scripts/init.mysql.gradle
   $ # Deploy on Tomcat 
   $ cp ./build/libs/JavaBridgeTemplate.war /var/lib/tomcat8/webapps/MyJavaBridge.jar
   ```
   
   Wait few seconds and check the [landing page](http://localhost:8080) for status and use the connection `localhost:8080/MyJavaBridge/servlet.phpjavabridge` in your bridge connection parameters.    
      
3. Option 3: Composer installable PHP-Java-bridge standalone server 

   This third option should only be considered for internal development or unit testing (Travis) 
     
    It can be installed in minutes and provides scripts to start and stop a standalone PHP-Java-bridge server. 
       
    ```console
    $ mkdir -p /my/path/pjbserver-tools
    $ cd /my/path/pjbserver-tools
    $ composer create-project --no-dev --prefer-dist "belgattitude/pjbserver-tools"
    $ ./bin/pjbserver-tools pjbserver:start -vvv ./config/pjbserver.config.php.dist
    ```
    
    The server will start on default port ***8089***. If you like to change it, create a local copy of `./config/pjbserver.config.php.dist`
    and refer it in the above command.
       
    Use the commands `pjbserver:stop`, `pjbserver:restart`, `pjbserver:status` to control or query the server status.
    
    See the [pjbserver-tools documentation](https://github.com/belgattitude/pjbserver-tools) for more options
    and dependencies management.  
             

## Notes about web.xml configuration 

If you use the second installation option: *barebone* [PHPJavaBridge](https://github.com/belgattitude/php-java-bridge) from sources,
you can customize the `web.xml` for your own needs, like setting the `php-cgi` executable or removing
the PHPCGIServlet registration. The default one provided with the PHPJavaBridge correspond to:
 
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE web-app PUBLIC "-//Sun Microsystems, Inc.//DTD Web Application 2.3//EN" "http://java.sun.com/dtd/web-app_2_3.dtd">

<web-app>
    <!-- support for JEE cluster, remove this line if you want to put non-serializable objects into the java_session() -->
    <distributable/>

    <context-param>
        <!-- Option 1: Only if you want to rely on the default php-cgi binary
        <param-name>prefer_system_php_exec</param-name>
        <param-value>On</param-value>
        -->        

        <!-- Option 2: set location of the php-cgi binary -->
        <param-name>php_exec</param-name>
        <param-value>/usr/bin/php-cgi</param-value>
    </context-param>

    <!-- Handle PHP urls which cannot be expressed using a standard servlet spec 2.2 url-pattern, 
    e.g.: *.php/delete/from?what=that You may remove this and the filter-mapping below -->
    <filter>
        <filter-name>PhpCGIFilter</filter-name>
        <filter-class>php.java.servlet.PhpCGIFilter</filter-class>
    </filter>
    <filter-mapping>
        <filter-name>PhpCGIFilter</filter-name>
        <url-pattern>/*</url-pattern>
    </filter-mapping>

    <!-- Attach the JSR223 script factory to the servlet context -->
    <listener>
        <listener-class>php.java.servlet.ContextLoaderListener</listener-class>
    </listener>

    <!-- PHP Servlet: back-end for Apache or IIS -->
    <servlet>
        <servlet-name>PhpJavaServlet</servlet-name>
        <servlet-class>php.java.servlet.PhpJavaServlet</servlet-class>
    </servlet>

    <!-- PHP CGI servlet: when IIS or Apache are not available -->
    <servlet>
        <servlet-name>PhpCGIServlet</servlet-name>
        <servlet-class>php.java.servlet.fastcgi.FastCGIServlet</servlet-class>
    </servlet>

    <!-- PHP Servlet Mapping -->
    <servlet-mapping>
        <servlet-name>PhpJavaServlet</servlet-name>
        <url-pattern>*.phpjavabridge</url-pattern>
    </servlet-mapping>

    <!-- PHP CGI Servlet Mapping -->
    <servlet-mapping>
        <servlet-name>PhpCGIServlet</servlet-name>
        <url-pattern>*.php</url-pattern>
    </servlet-mapping>

    <!-- Welcome files -->
    <welcome-file-list>
        <welcome-file>index.php</welcome-file>
    </welcome-file-list>
</web-app>
```


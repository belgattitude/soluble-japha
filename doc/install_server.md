## Install PHP-Java-bridge server (Java)

### Requirements
 
- Installed [JRE or JDK 7+](./server/install_java.md).

## Introduction

To communicate with the JVM, `soluble/japha` requires a bridge server running on your local machine (or network). 

First you need to ensure you have a valid JDK installed, see the [*nix java install guide](./server/install_java.md). 
You can test if you have a JDK/JRE installed by typing :

```sh
$ java -version 
``` 

## Installation 
 
To improve developer experience you can use one of the following methods to install the bridge.

Be aware that ***the port used for java bridge should not be public*** for security reasons.

### Option 1: PHP-Java-bridge standalone server *(development or small apps)*

For development (*nix systems), unit-tests or small apps, you can use the [pjbserver-tools](https://github.com/belgattitude/pjbserver-tools) 
package. 
 
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

See the [pjbserver-tools documentation](https://github.com/belgattitude/pjbserver-tools) for more options.  
         
*Note: The standalone server is perfect for development, but if you're looking for a more robust production-ready install method,
please use the J2EE option.*  
       
### Option 2: PHP-Java-bridge in a J2EE/Tomcat server *(production or dev)*

Tomcat installation is recommended for production environments. It's fast and robust. 

#### Step 1: Install Tomcat.

See the [Tomcat installation documentation](./server/install_tomcat.md) for *nix systems. 

#### Step 2: Create a java bridge deployable .war file.
 
Create a deploybale servlet, you can use the following snippet :

```console
# Download 
$ wget "http://downloads.sourceforge.net/project/php-java-bridge/Binary%20package/php-java-bridge_6.2.1/JavaBridgeTemplate621.war?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fphp-java-bridge%2Ffiles%2FBinary%2520package%2Fphp-java-bridge_6.2.1%2F&ts=1415114437&use_mirror=softlayer-ams" -O JavaBridgeTemplate621.war;

# Extract jars into a war structure
$ mkdir -p ./pjb_war/dist/WEB-INF/lib;
$ unzip -o -j JavaBridgeTemplate621.war WEB-INF/lib/*.jar -d ./pjb_war/dist/WEB-INF/lib;


# Add as many dependencies as you want in the WEB-INF/lib directory
# For example mysql connector, jasper report, custom fonts...

# See also web.xml configuration at the end of document.

# Bundle a war file (the name phpjavabridge-bundle can be changed)
$ cd ./pjb_war;
$ jar -cvf phpjavabridge-bundle.war .
```

#### Step 3: Deploying the WAR file on your tomcat server

Use the tomcat admin interface to upload the war file or simply drop the javabridge-bundle.war into /var/lib/tomcat7/webapps/ and reload.

#### Notes about web.xml configuration 

The standard web.xml configuration can be used. Alternativly you can provide your own file before building .war file. 

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


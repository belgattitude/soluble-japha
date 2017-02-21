# Javabridge server installation

In order to talk with the JVM, `soluble-japha` requires installation of
a specific java service called the Javabridge server. It acts as an intermediate
between the Java and PHP runtimes and can be build, customized and runned in minutes
straight from the command line. 

## Requirements

- An installed [OpenJDK or OracleJDK, 7 or 8+](./server/install_java.md) *(JRE is sufficient for running)*.
- An optional but recommended [Apache Tomcat](./server/install_tomcat.md) server for deployments 
  *(or any servlet 2.5+ spec container)*.

## Java installation

On ubuntu based systems, you can install java by typing:
 
```shell
$ sudo apt-get install openjdk-8-jdk  
```

*For other recipes and systems, refer to the [extended java installation notes](./server/install_java.md).*

------

## Quick install <small>(on Tomcat)</small>

!!! note    
    The instructions below are based on the barebone [PHP/Javabridge](https://github.com/belgattitude/php-java-bridge) version
    and rely on an installed Apache Tomcat server which is the recommended way to run the bridge. 
    To learn more see the [Tomcat installation notes](./server/install_tomcat.md) or jump 
    to the [Alternative installation section](#alternative-install) for 
    standalone mode *(embedded tomcat)*. 

### Build and customize

Replace the version below by the latest [Javabridge release](https://github.com/belgattitude/php-java-bridge/releases): [![Maven Central](https://maven-badges.herokuapp.com/maven-central/io.soluble.pjb/php-java-bridge/badge.svg)](https://maven-badges.herokuapp.com/maven-central/io.soluble.pjb/php-java-bridge)

```shell
# 1. Download and unzip the latest php-java-bridge sources
$ wget https://github.com/belgattitude/php-java-bridge/archive/6.2.12.zip -O pjb.zip
$ unzip pjb.zip && cd php-java-bridge-6.2.12
# 2. Customize and build your own bridge:
#    Example below contains some pre-made gradle init scripts
#    to include jasperreports and mysql-connector libraries to the
#    build. They are optional, remove the (-I) parts or provide
#    your own.       
$ ./gradlew war \
         -I init-scripts/init.jasperreports.gradle \
         -I init-scripts/init.mysql.gradle
# The build files are generated in the '/build/libs' folder.          
```

!!! tip
    As shown above, adding library dependencies can be done easily through gradle
    init-scripts arguments (-I). To learn mode, have a look to 
    [some pre-made init scripts](https://github.com/belgattitude/php-java-bridge/blob/master/init-scripts/README.md) 
    included in the latest distribution or provide your own.

### Deploy and run

Ensure Tomcat is [installed](./server/install_tomcat.md) or 
quickly run `sudo apt-get install tomcat8` on Ubuntu based systems,
then copy the builded war file in the tomcat webapps folder.

```shell
# 3. Deploy or update the servlet on Tomcat:
#    Example below is based on Ubuntu with Tomcat8
#    and can be applied in the same way for other systems,
#    just replace the webapp directory location.
$ cp ./build/libs/JavaBridgeTemplate.war /var/lib/tomcat8/webapps/MyJavaBridge.war       
```

Tomcat will automatically deploy and register the servlet. Wait few seconds and point 
your browser to [http://localhost:8080/MyJavaBridge](http://localhost:8080/MyJavaBridge) 
*(note the `/MyJavaBridge` uri corresponds to the deployed war filename, easily changeable)* 
and check the landing page:


![](./images/bridge_landing.png "Landing screenshot")

Once checked, jump to the [Getting started and how to connect section](./bridge_connection.md). 

!!! danger
    For obvious security reasons, the Javabridge server cannot be exposed on a public
    network. It must be limited to interactions on the same host/network
    and should be runned under the same user (not root) as the php client. (Bind Tomcat to localhost 
    or setup a firewall where applicable).   


------

## Alternative install

!!! summary    
    
    This install method should be considered whenever you need a standalone binary or
    intend to develop your own java classes in a sprint-boot architecture. 

This alternative install method is based on the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot) 
starter kit. In comparison to the quick installation above:
 
- Provide also a binary runnable from the cli (standalone / embedded Tomcat 8.5+).
- Foundation to add write extra Java code in the [spring-boot](https://projects.spring.io/spring-boot/) way (skeleton).
- Heavier size (approx 10Mb more than the barebone version: 1Mb, 15Mb more for standalone)
- No performance regressions measured from the barebone PHP/Javabridge
- Might not include the latest release of the bridge (not as frequently updated).

Please check the [pjb-starter-springboot](https://github.com/belgattitude/pjb-starter-springboot)
to get the latest instructions or follow the procedure below:    

       
### Build and customize

The instructions are very similar from the barebone PHP/Javabridge installation described above:
    
```shell
# 1. Clone the pjb-starter-springboot skeleton (-b to checkout a specific release)
$ git clone https://github.com/belgattitude/pjb-starter-springboot
$ cd pjb-starter-springboot
# 2. Customize and build your own bridge:
#    Example below contains some pre-made gradle init scripts
#    to include jasperreports and mysql-connector libraries to the
#    build. They are optional, remove the (-I) parts or provide
#    your own.       
$ ./gradlew build \
         -I init-scripts/init.jasperreports.gradle \
         -I init-scripts/init.mysql.gradle
# The build files are generated in the '/build/libs' folder.          
``` 

!!! tip
    As shown above, adding library dependencies can be done easily through gradle
    init-scripts arguments (-I). To learn mode, have a look to 
    [some pre-made init scripts](https://github.com/belgattitude/pjb-starter-sprinboot/blob/master/init-scripts/README.md) 
    included in the latest distribution or provide your own.


And check the `build\libs` directory for the following files:

| File          | Description   | Approx. size |
| ------------- | ------------- | ------------ |
| `JavaBridgeStandalone.jar`  | Standalone server with an embedded Tomcat 8.5+. | +/- 32Mb |
| `JavaBridgeTemplate.war`    | War file, ready to drop into Tomcat webapps folder. | +/- 12Mb |


### Run in standalone

You can easily run the a `JavaBridgeStandalone.jar` file from the cli:

```shell
# Run the PHPJavaBridge server in standalone
$ java -jar ./build/libs/JavaBridgeStandalone.jar -Dserver_port=8089   
```

Check the [landing page](http://localhost:8089) for status and
use the connection `localhost:8089/servlet.phpjavabridge` in your 
bridge connection parameters.

!!! tip
    As the standalone version embeds and runs on Tomcat 8.5+ you might wonder
    how it compares from a regular tomcat deployment? One important difference
    concerns the system integration and with the provided OS version:
     
    - Error and log files are maintained and rotated in standard directories.  
    - Automatic startup is provided out-of-the-box. 
    
    And if standalone gives a lot of freedom, it comes with the need to 
    control the service by yourself (supervisord or cli scripts...). Up to you 
    to decide which method is applicable for you, the pjb-starter-springboot
    offers both posibilities. 

### Run on Tomcat

Ensure Tomcat is [installed](./server/install_tomcat.md) or 
quickly run `sudo apt-get install tomcat8` on Ubuntu based systems,
then copy the builded war file in the tomcat webapps folder.

```shell
# 3. Deploy or update the servlet on Tomcat:
#    Example below is based on Ubuntu with Tomcat8
#    and can be applied in the same way for other systems,
#    just replace the webapp directory location.
$ cp ./build/libs/JavaBridgeTemplate.war /var/lib/tomcat8/webapps/MyJavaBridge.war       
```

Tomcat will automatically deploy and register the servlet. Wait few seconds and point 
your browser to [http://localhost:8080/MyJavaBridge](http://localhost:8080/MyJavaBridge) 
*(note the `/MyJavaBridge` uri corresponds to the deployed war filename, easily changeable)* 
and check the landing page:

!!! warning
    For obvious security reasons, the Javabridge server cannot be exposed on a public
    network. It must be limited to interactions on the same host/network
    and should be runned under the same user (not root) as the php client. (Bind Tomcat to localhost 
    or setup a firewall where applicable).   


## Composer install

As a third alternative, the [pjbserver-tools standalone server](https://github.com/belgattitude/pjbserver-tools) 
offer a pre-made server binary and can be installed straight from composer.

!!! warning
    The pjbserver-tools package is not fit for production usage (yet), its best use is for
    unit-tests (travis...) or local development only.  



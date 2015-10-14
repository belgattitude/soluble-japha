
# Installing on Ubuntu 14.04+

## Installing Tomcat

The recommended setup is based on Tomcat 7 Java Servlet engine. 
 

```bash
sudo apt-get install tomcat7 tomcat7-admin
```

### Optionally set admin credentials to tomcat users.xml configuration

```bash
sudo vi /etc/tomcat7/tomcat-users.xml
```

Replace username and password in the <tomcat-users> section

```xml
<tomcat-users>
    <user username="admin" password="password" roles="manager-gui,admin-gui"/>
</tomcat-users>
```

Restart tomcat to apply changes 'sudo service tomcat7 restart'

### Optionally increase JVM memory on startup

```bash
sudo vi /etc/default/tomcat7
```

And modify VM execution parameters (-Xmx and -XX params)

```bash
JAVA_OPTS="-Djava.security.egd=file:/dev/./urandom -Djava.awt.headless=true -Xmx512m -XX:MaxPermSize=256m -XX:+UseConcMarkSweepGC"
```

Restart tomcat to apply changes 'sudo service tomcat7 restart'.

### Testing installation

Open your browser to "http://localhost:8080", a page should say 'It works'

The tomcat manager interface can be located at "http://localhost:8080/manager"


### Resources

http://tomcat.apache.org/
https://www.digitalocean.com/community/tutorials/how-to-install-apache-tomcat-7-on-ubuntu-14-04-via-apt-get


## Installing phpjavabridge servlet on Tomcat server

### Download JavaBridge archive

Download the latest JavaBridgeTemplate621.war on sourceforce (http://sourceforge.net/projects/php-java-bridge/files/Binary%20package/php-java-bridge_6.2.1/)

### Creating a WAR file

Bundle a minimal phpjavabridge into a deployable war file

```bash

# Download 
wget "http://downloads.sourceforge.net/project/php-java-bridge/Binary%20package/php-java-bridge_6.2.1/JavaBridgeTemplate621.war?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fphp-java-bridge%2Ffiles%2FBinary%2520package%2Fphp-java-bridge_6.2.1%2F&ts=1415114437&use_mirror=softlayer-ams" -O JavaBridgeTemplate621.war;

# Extract jars into a war structure
mkdir -p ./pjb_war/dist/WEB-INF/lib;
unzip -o -j JavaBridgeTemplate621.war WEB-INF/lib/*.jar -d ./pjb_war/dist/WEB-INF/lib;

# Add as many dependencies as you want in the WEB-INF/lib directory
# For example mysql connector, jasper report...

# Bundle a war file (the name phpjavabridge-bundle can be changed)
cd ./pjb_war;
jar -cvf phpjavabridge-bundle.war .
```

### Deploying the WAR file on your tomcat server

Use the tomcat admin interface to upload the war file or simply drop the javabridge-bundel.war into /var/lib/tomcat7/webapps/.


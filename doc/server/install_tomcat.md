# Tomcat J2EE install

## Installation on Debian/Ubuntu 14.04+ 

### Install

First install the tomcat server

```console
$ sudo apt-get install tomcat7 tomcat7-admin
```

### Configuration

#### Tomcat administration interface (optional)

Optionally set admin credentials to tomcat users.xml configuration

```console
$ sudo vi /etc/tomcat7/tomcat-users.xml
```

Replace username and password in the <tomcat-users> section

```xml
<tomcat-users>
    <user username="admin" password="password" roles="manager-gui,admin-gui"/>
</tomcat-users>
```

Restart tomcat to apply changes with `sudo service tomcat7 restart` or `sudo /etc/init.d/tomcat7 restart`.

#### JVM parameters (optional)

Optionally increase the JVM memory

```console
$ sudo vi /etc/default/tomcat7
```

And modify VM execution parameters (-Xmx and -XX params)

```console
$ JAVA_OPTS="-Djava.security.egd=file:/dev/./urandom -Djava.awt.headless=true -Xmx512m -XX:MaxPermSize=256m -XX:+UseConcMarkSweepGC"
```

Restart tomcat to apply changes with `sudo service tomcat7 restart` or `sudo /etc/init.d/tomcat7 restart`.

#### Setting JDK (optional)

```console
$ sudo vi /etc/default/tomcat7
```

And modify `JAVA_HOME`, for example with latest [Oracle JDK](./install_java.md)

```console
JAVA_HOME=/usr/lib/jvm/java-8-oracle
```

Restart tomcat to apply changes with `sudo service tomcat7 restart` or `sudo /etc/init.d/tomcat7 restart`.

## Testing installation

Open your browser to "http://localhost:8080", a page should say 'It works'

The tomcat manager interface can be located at "http://localhost:8080/manager"

## Resources

http://tomcat.apache.org/
https://www.digitalocean.com/community/tutorials/how-to-install-apache-tomcat-7-on-ubuntu-14-04-via-apt-get


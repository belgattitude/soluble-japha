## Tomcat J2EE installation

### For Debian, Ubuntu 14.04 / 16.04

First install the tomcat server

```shell
$ sudo apt-get install tomcat8 tomcat8-admin
```

### For other systems

Google is your best friend ;)

## Configuration

### Ubuntu 14.04/16.04

#### Admin interface (optional)

An optional but nice move to do is to configure the Tomcat admin interface : 

```console
$ sudo vi /etc/tomcat8/tomcat-users.xml
```

Replace username and password in the <tomcat-users> section

```xml
<tomcat-users>
    <user username="admin" password="password" roles="manager-gui,admin-gui"/>
</tomcat-users>
```

Restart tomcat to apply changes with `sudo service tomcat8 restart` or `sudo /etc/init.d/tomcat7 restart`.

#### Setting Oracle JDK (optional)

```console
$ sudo vi /etc/default/tomcat8
```

And modify `JAVA_HOME`, for example with latest [Oracle JDK](./install_java.md)

```console
JAVA_HOME=/usr/lib/jvm/java-8-oracle
```

Restart tomcat to apply changes with `sudo service tomcat8 restart` or `sudo /etc/init.d/tomcat8 restart`.

## Testing installation

Open your browser to "http://localhost:8080", a page should say 'It works'

The tomcat manager interface can be located at "http://localhost:8080/manager"

## Resources

http://tomcat.apache.org/
https://www.digitalocean.com/community/tutorials/how-to-install-apache-tomcat-7-on-ubuntu-14-04-via-apt-get


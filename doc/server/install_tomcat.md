# Tomcat setup

This document provides some recipes and examples for installing Tomcat on your system. Refer to 
the official [Tomcat](http://tomcat.apache.org/) homepage for updated documentation and 
[installation instructions](https://tomcat.apache.org/tomcat-8.5-doc/setup.html). 

Note that Tomcat requires [Java](./install_java.md) prior to installation.  
 
## Installation
 
### Ubuntu/Debian  
  
On Ubuntu/Debian Tomcat can be installed easily from the command line. Both tomcat7 and tomcat8 versions
are available, feel free to replace `8` by `7` in the following examples: 

```shell
$ sudo apt-get install tomcat8 tomcat8-admin
```

The tomcat server listens by default on port `8080` and its `webapps` folder is located in `/var/lib/tomcat8/webapps`.  
 
 
### Apple OSX 

Tomcat 8 can be installed with homebrew, open a terminal and type:

```shell
$ brew install tomcat
```

The tomcat server listens by default on port `8080` and its `webapps` folder is located in `/usr/local/Cellar/tomcat/[version]/libexec/webapps/`.  

If you want to check the service, run

```shell
$ brew services list
```

### Windows

Download the tomcat windows binaries on the Tomcat [download page](https://tomcat.apache.org/download-80.cgi) and follow 
instructions.


### Docker *(multiplatform)*

Alternatively you can pull the [official tomcat](https://hub.docker.com/_/tomcat/) image.
 
```shell
$ docker run -it --rm tomcat:8.0 
```

And listens by default on `8088` port.



## Configuration

### Ubuntu/Debian

#### Admin interface (optional)

An optional but nice move to do is to configure the Tomcat admin interface: 


```console
$ sudo vi /etc/tomcat8/tomcat-users.xml
```

Replace username and password in the <tomcat-users> section

```xml
<tomcat-users>
    <user username="admin" password="password" roles="manager-gui,admin-gui"/>
</tomcat-users>
```

Restart tomcat to apply changes with `sudo service tomcat8 restart` or `sudo /etc/init.d/tomcat8 restart`.

#### Setting Oracle JDK (optional)

On Ubuntu systems:

```console
$ sudo vi /etc/default/tomcat8
```

And modify `JAVA_HOME`, for example with latest [Oracle JDK](./install_java.md)

```console
JAVA_HOME=/usr/lib/jvm/java-8-oracle
```

Restart tomcat to apply changes with `sudo service tomcat8 restart` or `sudo /etc/init.d/tomcat8 restart`.

#### Adding more memory

On Ubuntu systems:

```shell
$ vi /etc/default/tomcat8
```

Look for the Xmx default at 128m and increase 

```
JAVA_OPTS="-Djava.awt.headless=true -Xmx512m -XX:+UseConcMarkSweepGC"
```

then restart Tomcat

```shell
sudo service tomcat8 restart
```

## Testing installation

Open your browser to "http://localhost:8080", a page should say 'It works'

The tomcat manager interface can be located at "http://localhost:8080/manager"

## Resources

http://tomcat.apache.org/
https://www.digitalocean.com/community/tutorials/how-to-install-apache-tomcat-7-on-ubuntu-14-04-via-apt-get


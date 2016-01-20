# Java JDK install

## For Linux derivatives

### Ubuntu 

On Ubuntu, you can either choose the default OpenJdk or
install the Oracle JDK through a additional software repository. The latter method 
is preferred. Starting with Ubuntu 14.04 both methods allow to install different 
JDK versions and let them co-exists on your system.

1. Option 1: OpenJdk (maintained by canonical)

   OpenJdk versions 7 and 8 can be installed through the official repos starting Ubuntu trusty.

   ```console
   $ sudo apt-get install openjdk-8-jdk
   ```

   To set the system wide default jdk, simply launch the `update-alternatives` command :

   ```console
   $ sudo update-alternatives --config java
   ```


2. Option 2: Oracle JDK (preferred - most compatible)

   The Oracle JDK is available through an external repo.

   ```console
   $ sudo apt-get install python-software-properties
   $ sudo add-apt-repository ppa:webupd8team/java
   $ sudo apt-get update
   $ sudo apt-get install oracle-java8-installer
   ```
   
   To set the system wide default jdk, simply install the `oracle-javaX-set-default` package, where X stands for the version you need.

   ```console
   $ sudo apt-get install oracle-java8-set-default
   ```

### Redhat / Centos

On redhat systems,

1. Option 1: OpenJdk 

   OpenJdk versions 6 and 7 can be installed through the official redhat repos

   ```console
   $ yum install java-1.7.0-openjdk-devel
   ```
   
   See also this [question](https://access.redhat.com/documentation/en-US/JBoss_Enterprise_Application_Platform/6/html/Installation_Guide/Install_OpenJDK_on_Red_Hat_Enterprise_Linux.html).

2. Options 2 : Oracle JDK

   *For Redhat subscribed users, you can refer to this [solution](https://access.redhat.com/solutions/732883).*


## Windows and Mac OS/X

   Platform binaries are available at the [official oracle page](http://www.oracle.com/technetwork/java/javase/downloads/index.html)
   
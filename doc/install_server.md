# Install PHP-Java-bridge server (Java)

## Introduction

To communicate with the JVM, `soluble/japha` requires a server running on your local machine. 

For quick and easy installation, you can use the [pjbserver-tools standalone server]().   
For production Apache Tomcat server is highly recommended and should be supported by most hosting providers.

Alternatively the phpjavabridge standalone client can be easily installed on Linux 
using the `pjbserver-tools` package.

## Requirements

- A Java virtual machine (JRE or JDK)
- The PHPJavaBridge server (standalone or in a servlet container)

## Java install

See [Java installation instructions](./server/install_java.md).

## PHPJava bridge standalone server (development)

For development usage, see the [Standalone docs](./server/install_standalone.md)

## Tomcat / J2EE (development and production)

For production or development usage, see the [Tomcat docs](./server/install_tomcat.md)

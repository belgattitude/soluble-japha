# Servlet context

If the bridge is deployed on Tomcat (not the standalone version), you can access the servlet context through 
the internal driver:

## Example

```php

<?php

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;

$ba = new BridgeAdapter([
                           'driver' => 'Pjb62',  
                           'servlet_address' => 'localhost:8089/servlet.phpjavabridge'
                        ]);

// $context is either 
//   JavaObject: Java('io.soluble.pjb.servlet.HttpContext') - for soluble/php-java-bridge 6.2.11+   
//   JavaObject: Java('php.java.servlet.HttpContext') - for original php-java-bridge 6.2.1
// @see http://docs.soluble.io/php-java-bridge/api/index.html?io/soluble/pjb/servlet/HttpContext.html

$context = $adapter->getDriver()->getContext();


// $httpServletRequest is either
//   JavaObject: Java('io.soluble.pjb.servlet.RemoteHttpServletRequest') - for soluble/php-java-bridge 6.2.11+
//   JavaObject: Java('php.java.servlet.RemoteServletRequest') - for original php-java-bridge 6.2.1
// @see http://docs.soluble.io/php-java-bridge/api/index.html?io/soluble/pjb/servlet/RemoteHttpServletContextFactory.html

$httpServletRequest = $context->getHttpServletRequest();


// $servlet is either
//   JavaObject: Java('io.soluble.pjb.servlet.PhpJavaServlet') object for soluble/php-java-bridge 6.2.11+
//   JavaObject: Java('php.java.servlet.PhpJavaServlet') - for original php-java-bridge 6.2.1
// @see http://docs.soluble.io/php-java-bridge/api/index.html?io/soluble/pjb/servlet/PhpJavaServlet.html
$servlet = $context->getServlet();


// $servletContext on Tomcat would be
//   JavaObject: org.apache.catalina.core.ApplicationContextFacade
$servletContext = $context->getServletContext();

// $servletConfig on Tomcat would be
//   JavaObject: 'org.apache.catalina.core.StandardWrapperFacade
$servletConfig = $context->getServlet()->getServletConfig();

```
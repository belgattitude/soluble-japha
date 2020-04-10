<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2020 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\DriverInterface;
use Soluble\Japha\Bridge\Exception\JavaException;
use Soluble\Japha\Interfaces\JavaObject;
use PHPUnit\Framework\TestCase;

class DriverContextServletTest extends TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var DriverInterface
     */
    protected $driver;

    protected function setUp()
    {
        \SolubleTestFactories::startJavaBridgeServer();
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->adapter = new Adapter([
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ]);
        $this->driver = $this->adapter->getDriver();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetServlet()
    {
        // The servlet context allows to call
        // methods present in on the servlet side
        // Check issue https://github.com/belgattitude/soluble-japha/issues/26
        // for more information

        $context = $this->driver->getContext();
        try {
            $servletContext = $context->getServlet();
        } catch (JavaException $e) {
            $msg = $e->getMessage();
            if ($e->getJavaClassName() == 'java.lang.IllegalStateException' &&
                preg_match('/PHP not running in a servlet environment/', $msg)) {
                // Basically mark this test as skipped as the test
                // was made on the standalone server
                $this->markTestIncomplete('Retrieval of servlet context is not supported with the standalone server');

                return;
            } else {
                throw $e;
            }
        }

        self::assertInstanceOf(JavaObject::class, $servletContext);

        $className = $this->driver->getClassName($servletContext);

        $supported = [
            // Before 6.2.11 phpjavabridge version
            'php.java.servlet.PhpJavaServlet',
            // From 6.2.11 phpjavabridge version
            'io.soluble.pjb.servlet.PhpJavaServlet'
        ];

        self::assertContains($className, $supported);

        //  From javax.servlet.GenericServlet

        $servletName = $servletContext->getServletName();
        self::assertInstanceOf(JavaObject::class, $servletName);
        self::assertEquals('java.lang.String', $this->driver->getClassName($servletName));
        self::assertEquals('phpjavaservlet', strtolower((string) $servletName));

        $servletInfo = $servletContext->getServletInfo();
        self::assertInstanceOf(JavaObject::class, $servletInfo);
        self::assertEquals('java.lang.String', $this->driver->getClassName($servletInfo));

        $servletConfig = $servletContext->getServletConfig();
        self::assertInstanceOf(JavaObject::class, $servletConfig);

        // on Tomcat could be : org.apache.catalina.core.StandardWrapperFacade
        //self::assertEquals('org.apache.catalina.core.StandardWrapperFacade', $this->driver->getClassName($servletConfig));

        $servletContext = $context->getServletContext();

        $paramNames = $servletContext->getInitParameterNames();
        //echo $this->driver->getClassName($paramNames);
        self::assertInstanceOf(JavaObject::class, $paramNames);
    }

    public function testGetServletOnTomcat()
    {
        $context = $this->driver->getContext();
        try {
            $servletContext = $context->getServlet();
        } catch (JavaException $e) {
            $msg = $e->getMessage();
            if ($e->getJavaClassName() == 'java.lang.IllegalStateException' &&
                preg_match('/PHP not running in a servlet environment/', $msg)) {
                // Basically mark this test as skipped as the test
                // was made on the standalone server
                $this->markTestIncomplete('Retrieval of servlet context is not supported with the standalone server');

                return;
            } else {
                throw $e;
            }
        }

        $servletConfig = $servletContext->getServletConfig();
        self::assertEquals('org.apache.catalina.core.StandardWrapperFacade', $this->driver->getClassName($servletConfig));

        self::assertEquals('org.apache.catalina.core.ApplicationContextFacade', $this->driver->getClassName($context->getServletContext()));
    }
}

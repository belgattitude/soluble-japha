<?php

namespace SolubleTest\Japha\Bridge;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\DriverInterface;
use Soluble\Japha\Bridge\Exception\JavaException;
use Soluble\Japha\Interfaces\JavaObject;

class DriverSessionTest extends \PHPUnit_Framework_TestCase
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
        //$this->markTestSkipped('Not yet implemented');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testJavaSessionType()
    {
        try {
            $session = $this->driver->getJavaSession();
            $this->assertInstanceOf(JavaObject::class, $session);
        } catch (JavaException $e) {
            $cls = $e->getJavaClassName();

            if ($cls == 'java.lang.IllegalStateException') {
                $this->markTestSkipped('Skipped session test: Probably under tomcat -> Cannot create a session after the response has been committed');
            } else {
                $this->assertTrue(false, "Cannot test session type: ($cls)");
            }
        }
    }

    public function testJavaSession()
    {
        try {
            $session = $this->adapter->getDriver()->getJavaSession();
            $counter = $session->get('counter');
            if ($this->adapter->isNull($counter)) {
                $session->put('counter', 1);
            } else {
                $session->put('counter', $counter + 1);
            }
        } catch (JavaException $e) {
            $cls = $e->getJavaClassName();
            if ($cls == 'java.lang.IllegalStateException') {
                $this->markTestSkipped('Skipped session test: Probably under tomcat -> Cannot create a session after the response has been committed');
            } else {
                $this->assertTrue(false, "Cannot test session type: ($cls)");
            }
        }
    }
}

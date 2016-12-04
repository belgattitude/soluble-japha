<?php

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Soluble\Japha\Bridge\Driver\AbstractDriver;
use Soluble\Japha\Interfaces;
use Soluble\Japha\Bridge\Exception;

class Pjb62Driver extends AbstractDriver
{
    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * @var PjbProxyClient
     */
    protected $pjbProxyClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * <code>
     *
     * $ba = new Pjb62Driver([
     *     'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/servlet.phpjavabridge'
     *      //'java_default_timezone' => null,
     *      //'java_prefer_values' => true,
     *    ], $logger);
     *
     * </code>
     *
     * @param array           $options
     * @param LoggerInterface $logger
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ConnectionException
     */
    public function __construct(array $options, LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;

        try {
            $this->pjbProxyClient = PjbProxyClient::getInstance($options);
        } catch (Exception\ConnectionException $e) {
            $address = $options['servlet_address'];
            $msg = "Cannot connect to php-java-bridge server on '$address', server didn't respond.";
            $this->logger->critical("[soluble-japha] $msg (" . $e->getMessage() . ')');
            throw $e;
        } catch (Exception\InvalidArgumentException $e) {
            $msg = 'Invalid arguments, cannot initiate connection to java-bridge.';
            $this->logger->error("[soluble-japha] $msg (" . $e->getMessage() . ')');
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return underlying bridge client.
     *
     * @return PjbProxyClient
     */
    public function getClient()
    {
        return $this->pjbProxyClient;
    }

    public function connect()
    {
        if (!$this->connected) {
            $this->connected = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getJavaClass($class_name)
    {
        return $this->pjbProxyClient->getJavaClass($class_name);
    }

    /**
     * {@inheritdoc}
     */
    public function instanciate($class_name, $args = null)
    {
        //return $this->pjbProxyClient->getJavaClass($class_name, $args);
        if ($args === null) {
            return new Java($class_name);
        }

        return new Java($class_name, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(Interfaces\JavaType $javaObject, $method, array $args = [])
    {
        return $this->pjbProxyClient->invokeMethod($javaObject, $method, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->pjbProxyClient->getClient()->getContext();
    }

    /**
     * Return java servlet session.
     *
     * <code>
     * $session = $adapter->getDriver()->getJavaSession();
     * $counter = $session->get('counter');
     * if ($adapter->isNull($counter)) {
     *    $session->put('counter', 1);
     * } else {
     *    $session->put('counter', $counter + 1);
     * }
     * </code>
     *
     * @param array $args
     *
     * @return Interfaces\JavaObject
     */
    public function getJavaSession(array $args = [])
    {
        return $this->pjbProxyClient->getClient()->getSession();
    }

    /**
     * Inspect the class internals.
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return string
     */
    public function inspect(Interfaces\JavaObject $javaObject)
    {
        return $this->pjbProxyClient->inspect($javaObject);
    }

    /**
     * {@inheritdoc}
     */
    public function isInstanceOf(Interfaces\JavaObject $javaObject, $className)
    {
        return $this->pjbProxyClient->isInstanceOf($javaObject, $className);
    }

    /**
     * {@inheritdoc}
     */
    public function values(Interfaces\JavaObject $javaObject)
    {
        return $this->pjbProxyClient->getValues($javaObject);
    }

    /**
     * Return java bridge header.
     *
     * @param string $name
     * @param array  $array
     *
     * @return string|void
     */
    public static function getJavaBridgeHeader($name, array $array)
    {
        if (array_key_exists($name, $array)) {
            return $array[$name];
        }
        $name = "HTTP_$name";
        if (array_key_exists($name, $array)) {
            return $array[$name];
        }

        return;
    }

    /**
     * Cast internal objects to a new type.
     *
     * @param Interfaces\JavaObject|JavaType $javaObject
     * @param $cast_type
     *
     * @return mixed
     */
    public static function castPjbInternal($javaObject, $cast_type)
    {
        if (!$javaObject instanceof JavaType) {
            $first_char = strtoupper(substr($cast_type, 0, 1));
            switch ($first_char) {
                case 'S':
                    return (string) $javaObject;
                case 'B':
                    return (bool) $javaObject;
                case 'L':
                case 'I':
                    return (int) $javaObject;
                case 'D':
                case 'F':
                    return (float) $javaObject;
                case 'N':
                    return;
                case 'A':
                    return (array) $javaObject;
                case 'O':
                    return (object) $javaObject;
            }
        }

        return $javaObject->__cast($cast_type);
    }

    /**
     * {@inheritdoc}
     */
    public function cast(Interfaces\JavaObject $javaObject, $cast_type)
    {
        /* @todo see how can it be possible to clean up to new structure
            const CAST_TYPE_STRING  = 'string';
            const CAST_TYPE_BOOLEAN = 'boolean';
            const CAST_TYPE_INTEGER = 'integer';
            const CAST_TYPE_FLOAT   = 'float';
            const CAST_TYPE_ARRAY   = 'array';
            const CAST_TYPE_NULL    = 'null';
            const CAST_TYPE_OBJECT  = 'object';
         */
        $first_char = strtoupper(substr($cast_type, 0, 1));
        switch ($first_char) {
            case 'S':
                return (string) $javaObject;
            case 'B':
                return (bool) $javaObject;
            case 'L':
            case 'I':
                return (int) $javaObject;
            case 'D':
            case 'F':
                return (float) $javaObject;
            case 'N':
                return;
            case 'A':
                return (array) $javaObject;
            case 'O':
                return (object) $javaObject;
            default:
                throw new Exception\RuntimeException("Unsupported cast_type parameter: $cast_type");
        }
    }

    /**
     * Return object java class name.
     *
     * @throw Exception\UnexpectedException
     *
     * @param Interfaces\JavaObject $javaObject
     *
     * @return string
     */
    public function getClassName(Interfaces\JavaObject $javaObject)
    {
        $inspect = $this->inspect($javaObject);
        // [class java.sql.DriverManager:
        $matches = [];
        preg_match('/^\[class (.+)\:/', $inspect, $matches);
        if (!isset($matches[1]) || $matches[1] == '') {
            throw new Exception\UnexpectedException(__METHOD__ . ' Cannot determine class name');
        }

        return $matches[1];
    }
}

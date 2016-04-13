<?php

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Driver\AbstractDriver;
use Soluble\Japha\Interfaces;
use Soluble\Japha\Bridge\Exception;

class Pjb62Driver extends AbstractDriver
{
    /**
     * @var boolean
     */
    protected $connected = false;

    /**
     *
     * @var PjbProxyClient
     */
    protected $pjbProxyClient;

    /**
     *
     * Constructor
     *
     * <code>
     *
     * $ba = new Pjb62Driver([
     *     'servlet_address' => 'http://127.0.0.1:8080/javabridge-bundle/servlet.phpjavabridge'
     *      //'java_default_timezone' => null,
     *      //'java_disable_autoload' => false,
     *      //'java_prefer_values' => true,
     *      //'load_pjb_compatibility' => false
     *    ]);
     *
     * </code>
     *
     * @var array $options
     */
    public function __construct(array $options)
    {
        try {
            $this->pjbProxyClient = PjbProxyClient::getInstance($options);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return underlying bridge client
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
     *
     *
     * @param Interfaces\JavaObject $javaObject
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
                return (boolean) $javaObject;
            case 'L':
            case 'I':
                return (integer) $javaObject;
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
     * Return object java class name
     *
     * @throw Exception\UnexpectedException
     * @param Interfaces\JavaObject $javaObject
     * @return string
     */
    public function getClassName(Interfaces\JavaObject $javaObject)
    {
        $inspect = $this->inspect($javaObject);
        // [class java.sql.DriverManager:
        $matches = [];
        preg_match('/^\[class (.+)\:/', $inspect, $matches);
        if (!isset($matches[1]) || $matches[1] == '') {
            throw new Exception\UnexpectedException(__METHOD__ . " Cannot determine class name");
        }
        return $matches[1];
    }
}

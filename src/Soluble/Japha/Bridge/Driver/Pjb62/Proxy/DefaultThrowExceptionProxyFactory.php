<?php

namespace Soluble\Japha\Bridge\Driver\Pjb62\Proxy;

use Soluble\Japha\Bridge\Driver\Pjb62;
use Soluble\Japha\Bridge\Exception;

class DefaultThrowExceptionProxyFactory extends Pjb62\ThrowExceptionProxyFactory
{
    /**
     * @string
     */
    protected $defaultException = 'JavaException';

    /**
     * @var array
     */
    protected $msgPatternsMapping = [
        'NoSuchMethodException' => '/(php.java.bridge.NoSuchProcedureException)|(Cause: java.lang.NoSuchMethodException)/',
        'ClassNotFoundException' => '/Cause: java.lang.ClassNotFoundException/',
        //'InvalidArgumentException' => '/^Invoke failed(.*)php.java.bridge.NoSuchProcedureException/',
        'SqlException' => '/^Invoke failed(.*)java.sql.SQLException/',
    ];

    /**
     * @param Exception\InternalException $result
     *
     * @return Exception\JavaException
     */
    public function checkResult($result)
    {
        $exception = $this->getExceptionFromResult($result);
        throw $exception;
    }

    /**
     * @return \Exception
     */
    protected function getExceptionFromResult($result)
    {
        $found = false;
        $exceptionClass = '';

        $message = $result->message->__toString();

        foreach ($this->msgPatternsMapping as $exceptionClass => $pattern) {
            if (preg_match($pattern, $message)) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $exceptionClass = $this->defaultException;
        }

        $cls = '\\Soluble\\Japha\\Bridge\\Exception\\' . $exceptionClass;

        //$message, $javaCause, $stackTrace, $code=null, Exception $driverException=null, Exception $previous = null
        $cause = $message;
        // Public message, mask any login/passwords
        $message = preg_replace('/user=([^&\ ]+)|password=([^&\ ]+)/', '****', $message);
        $stackTrace = $result->getCause()->__toString();
        $code = $result->getCode();

        $driverException = null;
        if ($result instanceof \Soluble\Japha\Bridge\Exception\JavaExceptionInterface) {
            $driverException = $result;
        }

        // Getting original class name from cause
        preg_match('/Cause: ([^:]+):/', $message, $matches);
        if (count($matches) > 1) {
            $javaExceptionClass = $matches[1];
        } else {
            $javaExceptionClass = 'unkwown';
        }

        $e = new $cls($message, $cause, $stackTrace,
                      $javaExceptionClass, $code, $driverException, null);

        return $e;
    }
}

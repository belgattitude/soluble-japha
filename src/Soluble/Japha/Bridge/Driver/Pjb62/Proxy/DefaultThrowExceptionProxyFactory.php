<?php

declare(strict_types=1);

namespace Soluble\Japha\Bridge\Driver\Pjb62\Proxy;

use Psr\Log\LoggerInterface;
use Soluble\Japha\Bridge\Driver\Pjb62;
use Soluble\Japha\Bridge\Exception;
use Soluble\Japha\Bridge\Driver\Pjb62\Client;

class DefaultThrowExceptionProxyFactory extends Pjb62\ThrowExceptionProxyFactory
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
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
        'NoSuchFieldException' => '/Cause: java.lang.NoSuchFieldException/',
        //'NullPointerException' => '/Cause: java.lang.NullPointerException/'
    ];

    /**
     * @param Client          $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        parent::__construct($client);
        $this->logger = $logger;
    }

    /**
     * @param Pjb62\Exception\JavaException $result
     *
     * @throws Exception\JavaExceptionInterface
     */
    public function checkResult(Pjb62\Exception\JavaException $result): void
    {
        $exception = $this->getExceptionFromResult($result);
        throw $exception;
    }

    /**
     * @param Pjb62\Exception\JavaException $result
     *
     * @return Exception\JavaExceptionInterface
     */
    private function getExceptionFromResult(Pjb62\Exception\JavaException $result): Exception\JavaExceptionInterface
    {
        $found = false;
        $exceptionClass = '';

        $message = $result->__get('message')->__toString();

        foreach ($this->msgPatternsMapping as $exceptionClass => $pattern) {
            if (preg_match($pattern, $message)) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $exceptionClass = $this->defaultException;
        }

        $cls = '\\Soluble\\Japha\\Bridge\\Exception\\'.$exceptionClass;

        // Public message, mask any login/passwords
        $message = preg_replace('/user=([^&\ ]+)|password=([^&\ ]+)/', '****', $message);
        $stackTrace = $result->getCause()->__toString();
        $code = $result->getCode();

        $driverException = null;
        if ($result instanceof \Exception) {
            $driverException = $result;
        }

        // Getting original class name from cause
        preg_match('/Cause: ([^:]+):/', $message, $matches);
        if (count($matches) > 1) {
            $javaExceptionClass = $matches[1];
        } else {
            $javaExceptionClass = 'unkwown';
        }

        // Getting cause from message
        $tmp = explode('Cause: ', $message);
        if (count($tmp) > 1) {
            array_shift($tmp);
            $cause = trim(implode(', ', $tmp));
        } else {
            $cause = $message;
        }
        $e = new $cls(
            $message,
            $cause,
            $stackTrace,
            $javaExceptionClass,
            $code,
            $driverException,
            null
        );

        $this->logException($e, $exceptionClass);

        return $e;
    }

    private function logException(\Throwable $e, string $exceptionClass): void
    {
        $this->logger->error(sprintf(
            '[soluble-japha] Encountered exception %s: %s, code %s (%s)',
            $exceptionClass,
            $e->getMessage(),
            $e->getCode() ?? '?',
            get_class($e)
        ));
    }
}

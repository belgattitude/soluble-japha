<?php

namespace Soluble\Japha\Bridge\Exception;

use Exception;

class JavaException extends Exception implements JavaExceptionInterface
{
    /**
     *
     * @var string 
     */
    protected $cause;
    
    /**
     *
     * @var string
     */
    protected $stackTrace;

    
    /**
     * @var Exception
     */
    protected $driverException;

    /**
     * Constructor
     * 
     * @param string $message
     * @param string $javaCause
     * @param string $stackTrace
     * @param int $code
     * @param Exception $driverException
     * @param Exception $previous
     */
    public function __construct($message, $javaCause, $stackTrace, $code=null, Exception $driverException=null, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setCause($javaCause);
        $this->setStackTrace($stackTrace);
        if ($driverException !== null) {
            $this->setDriverException($driverException);
        }
    }
    
    /**
     * Set original exception as throw by the driver
     * @param Exception $driverException
     */
    protected function setDriverException(Exception $driverException)
    {
        $this->driverException = $driverException;
    }
    
    /**
     * Return underlying driver exception
     * 
     * @return Exception
     */
    public function getDriverException()
    {
        return $this->driverException;
    }
    
    /**
     * Set Java cause
     * @param string $cause
     */
    protected function setCause($cause)
    {
        $this->cause = $cause;
    }
    
    /**
     * Return Java cause
     * @return string
     */
    public function getCause()
    {
        return $this->cause;
    }
    
    /**
     * 
     * @param string $stackTrace
     */
    protected function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;
    }
    
    /**
     * Return Java stack trace
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }
}

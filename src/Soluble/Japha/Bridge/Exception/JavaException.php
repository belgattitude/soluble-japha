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
     * 
     * @param string $cause
     */
    public function setCause($cause)
    {
        $this->cause = $cause;
    }
    
    /**
     * 
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
    public function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;
    }
    
    /**
     * 
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }
}

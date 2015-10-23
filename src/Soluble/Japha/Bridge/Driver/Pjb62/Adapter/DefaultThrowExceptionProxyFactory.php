<?php

namespace Soluble\Japha\Bridge\Driver\Pjb62\Adapter;

use Soluble\Japha\Bridge\Driver\Pjb62;
use Soluble\Japha\Bridge\Exception;

class DefaultThrowExceptionProxyFactory extends Pjb62\ThrowExceptionProxyFactory
{
    /**
     *
     * @var array
     */
    protected $msgPatternsMapping = array(
        'NoSuchMethodException' => '/(php.java.bridge.NoSuchProcedureException)|(Cause: java.lang.NoSuchMethodException)/',
        'ClassNotFoundException' => '/Cause: java.lang.ClassNotFoundException/',
        //'InvalidArgumentException' => '/^Invoke failed(.*)php.java.bridge.NoSuchProcedureException/',
        'SqlException' => '/^Invoke failed(.*)java.sql.SQLException/',
        
    );
    
    
    /**
     * 
     * @param Exception\InternalException $result
     * @return Exception\JavaException
     */
    public function checkResult($result)
    {
        $exception = $this->getExceptionFromResult($result);
        throw $exception;
        
        /*
        dump(Pjb62\java_inspect($result));
        die();
        throw new DefaultJavaException ($result, $result->getCause()->message);
        dump($result->getCause()->message);
        die();
        dump($result->getCause()->getMessage());
        dump($result);
        die();
    throw new DefaultJavaException ($result, $result->getCause()->message);
        */
    }
  
  /**
   * 
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
          $exceptionClass = 'JavaException';
      }

      $cls = '\\Soluble\\Japha\\Bridge\\Exception\\' . $exceptionClass;
      
      //mask any login/passwords in message

      $message = preg_replace('/user=([^&\ ]+)|password=([^&\ ]+)/', '****', $message);
      
      $e = new $cls($message);
      $e->setStackTrace($result->getCause()->__toString());

      return $e;
  }
}

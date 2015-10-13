<?php

namespace Soluble\Japha\Bridge\Driver\Pjb621\Adapter;

use Soluble\Japha\Bridge\Driver\Pjb621\Exception;

class DefaultJavaException extends Exception\InternalException
{
    public function __construct($proxy, $message)
    {
        $this->message = $message;
        parent::__construct($proxy->__delegate, $message);
    }
}

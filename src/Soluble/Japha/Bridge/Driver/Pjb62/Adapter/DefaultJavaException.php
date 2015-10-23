<?php

namespace Soluble\Japha\Bridge\Driver\Pjb62\Adapter;

use Soluble\Japha\Bridge\Driver\Pjb62\Exception;

class DefaultJavaException extends Exception\InternalException
{
    public function __construct($proxy, $message)
    {
        $this->message = $message;
        parent::__construct($proxy->__delegate, $message);
    }
}

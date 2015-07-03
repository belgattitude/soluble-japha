<?php

namespace Soluble\Japha\Bridge\Driver;

interface ConnectionInterface
{
    public function getClient();
            
    
    public function connect();
}

<?php

namespace Soluble\Japha\Bridge\Driver;

interface ConnectionInterface
{
    /**
     * Return internal client (driver specific)
     * @return mixed
     */
    public function getClient();


    /**
     * @return void
     */
    public function connect();
}

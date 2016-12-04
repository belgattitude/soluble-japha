<?php

namespace Soluble\Japha\Bridge\Driver;

interface ConnectionInterface
{
    /**
     * Return internal client (driver specific).
     *
     * @return ClientInterface
     */
    public function getClient();

    public function connect();
}

<?php

namespace Soluble\Japha\Interfaces;

interface JavaType
{
    /**
     * Return java object id
     * @return int
     */
    public function __getJavaInternalObjectId();
}

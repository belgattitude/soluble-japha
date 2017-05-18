<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace Soluble\Japha\Interfaces;

/**
 * @method JavaClass getClass(string $type) Java('java.lang.Class')
 */
interface JavaType
{
    /**
     * Return java object id.
     *
     * @return int
     */
    public function __getJavaInternalObjectId();
}

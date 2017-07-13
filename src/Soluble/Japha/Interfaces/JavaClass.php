<?php

declare(strict_types=1);

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace Soluble\Japha\Interfaces;

/**
 * Those methods have been annotated from the java.lang.Class JDK8 API.
 *
 * @link https://docs.oracle.com/javase/8/docs/api/java/lang/Class.html
 *
 * @method \Soluble\Japha\Interfaces\JavaClass forName(string $name, boolean $initialize=null, \Soluble\Japha\Interfaces\JavaObject $loader=null)
 */
interface JavaClass extends JavaObject
{
    /**
     * Returns the name of the entity (class, interface, array class, primitive type, or void)
     * represented by this class object, as a string.
     *
     * @return string
     */
    public function getName(): string;
}

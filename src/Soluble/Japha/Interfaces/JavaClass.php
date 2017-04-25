<?php

namespace Soluble\Japha\Interfaces;

/**
 * @method JavaObject forName(string $name)
 */
interface JavaClass extends JavaObject
{
    /**
     * Returns the name of the entity (class, interface, array class, primitive type, or void)
     * represented by this Class object, as a string.
     *
     * @return string
     */
    public function getName();
}

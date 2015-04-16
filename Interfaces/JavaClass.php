<?php

namespace Soluble\Japha\Interfaces;

interface JavaClass extends JavaObject
{
    
    
    /**
     * Returns the name of the entity (class, interface, array class, primitive type, or void) 
     * represented by this Class object, as a String. 
     * 
     * @return JavaObject Java(java.lang.String)
     */
    public function getName();
    
    
}


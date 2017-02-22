# Limitations

## Decorators

### Overrides method

The following code cannot be written with the bridge:

```java
DecoratingComponent adapted = new DecoratingComponent() {
    @Override
	public SomeReturn someMethod(SomeArgument argument) {
	    return component.someMethod(argument);
    }
};
```

## Extending Java class

It is not possible to extend a Java class from a PHP one. Alternatively
you can implement [composition](https://en.wikipedia.org/wiki/Composition_over_inheritance) when
needed 
 
```php
<?php

namespace My\Helpers;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Interfaces;

class TimeZone
{
    protected $adapter;
    protected $timezone;
    
    function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->timezone = $adapter->java('java.util.TimeZone');
    }
    
    /**
     * Return default JVM/Java TimeZone.
     * @return Interfaces\JavaObject Java(java.util.TimeZone)
     */    
    function getDefault() {
        return $this->timeZoneClass->getDefault();        
    }
}    
``` 

## Lambdas

Closure and lambdas support is under considerations
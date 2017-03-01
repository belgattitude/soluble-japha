# Limitations

## Extending Java class

It is not possible to extend a Java class from a PHP one. Alternatively
you can implement [composition](https://en.wikipedia.org/wiki/Composition_over_inheritance) when
needed 
 
```php
<?php declare(strict_types=1)

namespace My\Helpers;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Interfaces\JavaObject;

class TimeZone
{   
    /**
     * @param JavaObject Java(java.util.TimeZone)
     */
    protected $timezone;
    
    /**
     * @return Adapter $adapter bridge adapter
     */
    public function __construct(Adapter $adapter) {
        $this->timezone = $adapter->java('java.util.TimeZone');
    }
    
    /**
     * Return default JVM/Java TimeZone.
     * @return JavaObject Java(java.util.TimeZone)
     */    
    public function getDefault(): JavaObject {
        return $this->timezone->getDefault();        
    }
}    
``` 

## Annotaions

### Overrides method

Unfortunately overriding a class method with annotations cannot 
be written with the bridge, see:

```java
CustomClass customObject = new CustomClass() {
    @Override
	public SomeReturn someMethod(SomeArgument argument) {
	    return component.someMethod(argument);
    }
};
```


## Lambdas

Closure and lambdas support is under considerations
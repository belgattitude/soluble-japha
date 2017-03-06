# Limitations

## Code auto-completion

Code auto-completion in IDE *(Netbeans, Eclipse, Idea, Atom...)* is not
supported. In other words, you cannot auto-complete methods on a remote 
Java object.

The general recommendation for now, is to at use the special notation
`Java('<java fqdn>')` as part of the parametere message in javadoc. See
the example below:  

```php
<?php declare(strict_types=1);

use Soluble\Japha\Interfaces\JavaObject;

class ExampleJavaDoc {

    /**
     * @var JavaObject Java('java.util.HashMap') the hashmap
     */
    protected $map;        
            
    /**
     * @param JavaObject $map Java('java.util.HashMap') the hashmap
     */
    public function __construct(JavaObject $map) {
        $this->map = $map;
    } 
    
    /**
     * @return JavaObject Java('java.util.HashMap') 
     */
    public function getMap(): JavaObject {
        return $this->map;   
    }
    
    /**
     * @throws \Exception 
     * @return int
     */
    public function getMapSize(): int {
        // This method cannot be autocompleted
        return $this->map->size();       
    }
}
```
 

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


## Lambdas

Closure and lambdas support is under considerations
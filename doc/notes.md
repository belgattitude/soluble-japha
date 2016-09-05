### Support

Please fill any issues on the [offical tracker](https://github.com/belgattitude/soluble-japha/issues). 
If you like to contribute, see the <a href="https://github.com/belgattitude/soluble-japha/blob/master/CONTRIBUTING.md">contribution guidelines</a>

### Status

Client API can be considered stable. 

Although semantic versioning will only be respected from v1.0.0 release, only minor modifications to the API will be considered at that point. 

[![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)
[![Build Status](https://travis-ci.org/belgattitude/soluble-japha.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-japha)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/)
[![License](https://poser.pugx.org/soluble/japha/license.png)](https://packagist.org/packages/soluble/japha)

At time of writing this document (Jul 16), the version 0.11.6 passes 57 unit tests and 582 assertions 
for a coverage of 58%. *(The low degree of coverage is mainly due to a lot of obsolete code in 
the pjb driver code that is still to be removed once reaching v1).*

### Changelog

Versions and changelog are documented on the <a href="https://github.com/belgattitude/soluble-japha/blob/master/CHANGELOG.md">changelog page</a>

### Personal comment

In this particular case, when two systems talk to each other (JVM, PHP) in a very dynamic way it's very 
difficult to cover all use cases and simply say that's super stable. 

I feel confident with the stability status of the API provided by soluble-japha client, but
for the server communication it seems fair to share some comments on the library :

> I've been using the original php-java-bridge with Tomcat for about 10 years. 
>
> At that time the php ecosystem was not really what it is now, and the java bridge
> allows solutions that I couldn't find in another way. So I started to use it,
> mainly for jasper reports integration. But also for syncing calendars, contacts
> with the Exchange MAPI *(EWS didn't exists yet)* in a CRM php-based application, even
> for connecting J2EE legacy java ERP systems when time to build an API was not option.
>  
> Not very sexy things but It allowed me to avoid extra steps like writing services 
> in Java (SOAP was popular) and keep the flexibility by not having a *too static* 
> contract between the php app and the java server.
>
> After few years playing with relatively small apps, I got the opportunity to integrate
> java bridge based solutions to some mission-critical and heavy load production. 
>
> And the truth is that don't even remember having a failure in the java php communication
> *(some servers still haven't been restarted for years)*. 
>
> Thus the implementation made by the [folks of the php-java-bridge project](http://php-java-bridge.sourceforge.net/pjb/contact.php) looks very reliable. 
> 
> Unfortunately it looks the original project maintainers seems to have found 
> different interests in life, which I totally understand and sincerely wish them the best.    
>
> Also PHP have grown to a very different language it was before and system communications are much
> more better handled, standardized and written nowadays.
>
> So in order to maintain my existing projects, I decided to fork, refactor and improve parts of the original 
> brige and release the ***soluble-japha*** client making it more friendly with the latest methodologies
> and trends in the php world. Till now, I've replaced it in most of my legacy projects without hurdle. 
>   
> This was about my experiences with stability of the bridge with a little context, I hope
> it may help you get a little idea whether it fits a particular project.  
>
> So I've published the code under MIT license, I have absolutely no idea if there will be any 
> interest today, but I feel it's good for the php-community to keep an opportunity to 
> develop in Java from PHP. And with all the creativity I've seen in the past, I believe there 
> might be some use and perhaps some fun with it. 
>

The original `Java.inc` client has been completely refactored to fit current trends in current PHP practices.

- New API (not backward compatible)

    All global functions have been removed (`java_*`) in favour of a more object oriented approach. 
    By doing so, the new API breaks compatibility with existing code (see the 
    [legacy compatibility guide](./doc/pjb62_compatibility.md) if you have code written against 
    the `Java.inc` original client), but offers the possibility to rely on different driver implementations 
    without breaking your code.

- PHP version and ecosystem

    - PHP7, HHVM ready.
    - Installable with composer
    - Compliant with latests standards: psr0, psr2, psr4

- Enhancements    
    
    - Removed global namespace pollution (java_* functions)
    - Removed global variables, functions and unscoped statics.
    - No more get_last_exception... (All exceptions are thrown with reference to context)
    - Autoloading performance (no more one big class, psr4 autoloader is used, less memory)
    - Removed long time deprecated features in Java.inc
    - By design, no more allow_url_fopen needed.
    
- Fixes
    
    - All notices, warnings have been removed
    - Some minor bugs found thanks to the unit tests suite

- Testing
   
    - All code is tested (phpunit, travis), analyzed (scrunitizer)


### Credits

This project wouldn't be possible without the PHPJavaBridge project leaders and contributors. 
See their official homepage on http://php-java-bridge.sourceforge.net/pjb/index.php.

# ExceptionBundle: cleaner exception handling for Symfony2 bundles

The InterNationsExceptionBundle helps you managing your bundles exceptions:

 - Generate exception subclasses from command line including a marker interface
 - Rewrite all global throw statements in your bundle with bundle specific exceptions

## Why should you care?

 - The simpler exception are distinguishable by type, the simpler exceptional conditions are handled
 - Having a bundle specific exception marker interface all bundles implement allows clients to simply catch all exceptions of a single bundle
 - Usually, creating all the sub classes by hand is cumbersome, ExceptionBundle helps a lot with it


## Generate bundle specific exception subclasses

This command will generate a bunch of exception

```
php app/console exception:generate app/src/MyVendor/MyBundle "MyVendor\MyBundle" ExceptionInterface RuntimeException DomainException
```

Will output:
```
Create directory app/src/MyVendor/MyBundle/Exception
Writing app/src/MyVendor/MyBundle/Exception/ExceptionInterface.php
Writing app/src/MyVendor/MyBundle/Exception/RuntimeException.php
Writing app/src/MyVendor/MyBundle/Exception/DomainException.php
```

```
ls app/src/MyVendor/MyBundle/Exception
ExceptionInterface.php  RuntimeException.php  DomainException.php
```

`cat app/src/MyVendor/MyBundle/Exception/RuntimeException.php`
```
<?php
namespace MyVendor\MyBundle\Exception;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
}
```

You can also specify the shortcut "spl" to subclass all Spl Exceptions

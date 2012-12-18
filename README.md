# ExceptionBundle[![Build Status](https://travis-ci.org/InterNations/ExceptionBundle.png?branch=master)](https://travis-ci.org/InterNations/ExceptionBundle)
## Clean exception handling for your Symfony 2 bundles

The `ExceptionBundle` helps you managing your bundles exceptions:

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

`ls app/src/MyVendor/MyBundle/Exception`

```
ExceptionInterface.php  RuntimeException.php  DomainException.php
```

`cat app/src/MyVendor/MyBundle/Exception/RuntimeException.php`
```php
<?php
namespace MyVendor\MyBundle\Exception;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
}
```

You can also specify the shortcut "spl" to subclass all Spl Exceptions

## Rewrite bundle exceptions
`ExceptionBundle` uses PHP Parser to rewrite all throw statements in a bundle code base.

`cat app/src/MyVendor/MyBundle/MyClass.php`

```php
<?php
namespace MyVendor\MyBundle;

use RuntimeException;
...
    throw new RuntimeException('Runtime error');
...
    throw new \InvalidArgumentException('Invalid argument');
```

`php app/console exception:rewrite app/src/MyVendor/MyBundle "MyVendor\MyBundle"`

Will output:

```
Found bundle specific exception class BadFunctionCallException
Found bundle specific exception class BadMethodCallException
Found bundle specific exception class DomainException
Found bundle specific exception class InvalidArgumentException
Found bundle specific exception class LengthException
Found bundle specific exception class LogicException
Found bundle specific exception class OutOfBoundsException
Found bundle specific exception class OutOfRangeException
Found bundle specific exception class OverflowException
Found bundle specific exception class RangeException
Found bundle specific exception class RuntimeException
Found bundle specific exception class UnderflowException
Found bundle specific exception class UnexpectedValueException
...............

------------------------------------------------------------
------------------------------------------------------------
SUMMARY
------------------------------------------------------------
------------------------------------------------------------
Files analyzed:               15
Files changed:                1
------------------------------------------------------------
"throw" statements found:     2
"throw" statements rewritten: 1
------------------------------------------------------------
"use" statements found:       1
"use" statements rewritten:   1
"use" statements added:       1
------------------------------------------------------------
"catch" statements found:     0
```

... and transform the source file to this one:

```php
<?php
namespace MyVendor\MyBundle;

use MyVendor\MyBundle\Exception\InvalidArgumentException;
use MyVendor\MyBundle\Exception\RuntimeException;

throw new RuntimeException('Runtime error');

throw new InvalidArgumentException('Invalid argument');
```

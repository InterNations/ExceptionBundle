# ExceptionBundle[![Build Status](https://travis-ci.org/InterNations/ExceptionBundle.svg)](https://travis-ci.org/InterNations/ExceptionBundle)
## Clean exception handling for your Symfony 2 bundles

`ExceptionBundle` helps you managing the exceptions of your bundle:

 - Generate exception subclasses from command line including a marker interface
 - Replace all global throw statements in a bundle with bundle specific exception classes

## Why should you care?

 - The simpler exceptions are distinguishable by type, the simpler exceptional conditions are handled
 - Providing a marker interface all bundle exception classes implement allows clients to dramatically simplify exception handling
 - Usually, creating all the exception sub classes by hand is cumbersome, `ExceptionBundle` can help you


## Usage

### Generate bundle specific exception subclasses

This command will generate a bunch of exceptions

```
php app/console exception:generate app/src/MyVendor/MyBundle "MyVendor\MyBundle" ExceptionInterface RuntimeException DomainException
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

### Rewrite bundle exceptions
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

Rewrites the code to:

```php
<?php
namespace MyVendor\MyBundle;

use MyVendor\MyBundle\Exception\InvalidArgumentException;
use MyVendor\MyBundle\Exception\RuntimeException;
...
    throw new RuntimeException('Runtime error');
...
    throw new InvalidArgumentException('Invalid argument');
...
```

## Installation

Add `internations/exception-bundle` to your `composer.json` and edit `AppKernel.php` like this:

```php
<?php
...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
        );

        if ($this->debug) {
            $bundles[] = new InterNations\Bundle\ExceptionBundle\InterNationsExceptionBundle();
        }
    }
}
```

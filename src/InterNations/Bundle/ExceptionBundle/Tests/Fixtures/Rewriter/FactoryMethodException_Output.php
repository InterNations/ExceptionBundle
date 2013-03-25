<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class FactoryMethodException
{
    public function throwExceptionFromFactory()
    {
        throw RuntimeException::factory();
    }
}

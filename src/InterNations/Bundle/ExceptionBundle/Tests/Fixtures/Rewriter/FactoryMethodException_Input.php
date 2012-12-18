<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use RuntimeException;

class FactoryMethodException
{
    public function throwExceptionFromFactory()
    {
        throw RuntimeException::factory();
    }
}
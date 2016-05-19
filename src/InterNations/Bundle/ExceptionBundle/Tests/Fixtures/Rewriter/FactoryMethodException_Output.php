<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class FactoryMethodException // @codingStandardsIgnoreLine
{
    public function throwExceptionFromFactory()
    {
        throw RuntimeException::factory();
    }
}

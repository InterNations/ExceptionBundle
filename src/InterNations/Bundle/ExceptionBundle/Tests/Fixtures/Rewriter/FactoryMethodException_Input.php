<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use RuntimeException;

class FactoryMethodException // @codingStandardsIgnoreLine
{
    public function throwExceptionFromFactory()
    {
        throw RuntimeException::factory();
    }
}

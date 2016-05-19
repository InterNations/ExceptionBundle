<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use FooException;

class UndeclaredException // @codingStandardsIgnoreLine
{
    public function throwException()
    {
        throw new FooException();
    }
}

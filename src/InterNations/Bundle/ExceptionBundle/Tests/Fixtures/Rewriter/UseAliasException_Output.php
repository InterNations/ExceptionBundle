<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException as MyRuntimeException;

class UseException // @codingStandardsIgnoreLine
{
    public function throwException()
    {
        throw new MyRuntimeException();
    }
}

<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use RuntimeException as MyRuntimeException;

class UseException // @codingStandardsIgnoreLine
{
    public function throwException()
    {
        throw new MyRuntimeException();
    }
}

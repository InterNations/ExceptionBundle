<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use RuntimeException as MyRuntimeException;

class UseException
{
    public function throwException()
    {
        throw new MyRuntimeException();
    }
}

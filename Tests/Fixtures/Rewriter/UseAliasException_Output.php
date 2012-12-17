<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException as MyRuntimeException;

class UseException
{
    public function throwException()
    {
        throw new MyRuntimeException();
    }
}

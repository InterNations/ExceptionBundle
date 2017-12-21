<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use RuntimeException;

class UseException
{
    public function throwException()
    {
        throw new RuntimeException();
    }

    public function throwFromVariable()
    {
        throw new $exception;
    }

    public function throwFromArrayIndex()
    {
        throw new $exception[0];
    }
}

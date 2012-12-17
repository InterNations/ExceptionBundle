<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use RuntimeException;

class UseException
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}
<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use FooException;

class UndeclaredException
{
    public function throwException()
    {
        throw new FooException();
    }
}

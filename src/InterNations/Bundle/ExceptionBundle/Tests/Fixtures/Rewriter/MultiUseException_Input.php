<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use stdClass,
    RuntimeException;

class FqException
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}

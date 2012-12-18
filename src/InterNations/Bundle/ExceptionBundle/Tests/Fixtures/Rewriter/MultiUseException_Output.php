<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use stdClass,
    InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class FqException
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}
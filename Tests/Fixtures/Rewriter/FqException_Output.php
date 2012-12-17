<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class FqException
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}

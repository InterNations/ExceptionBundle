<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class UseException
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}
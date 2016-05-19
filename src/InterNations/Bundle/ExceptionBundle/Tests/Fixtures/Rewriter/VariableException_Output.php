<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class UseException // @codingStandardsIgnoreLine
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

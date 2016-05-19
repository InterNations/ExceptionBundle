<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use stdClass, // @codingStandardsIgnoreLine
    InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

class FqException // @codingStandardsIgnoreLine
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}

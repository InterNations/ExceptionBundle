<?php
namespace InterNations\Bundle\ExceptionTestBundle; // @codingStandardsIgnoreLine

use stdClass, // @codingStandardsIgnoreLine
    RuntimeException;

class FqException // @codingStandardsIgnoreLine
{
    public function throwException()
    {
        throw new RuntimeException();
    }
}

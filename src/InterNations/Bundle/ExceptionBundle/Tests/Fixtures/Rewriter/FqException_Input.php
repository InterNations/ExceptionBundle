<?php // @codingStandardsIgnoreStart
namespace InterNations\Bundle\ExceptionTestBundle;

class FqException
{
    public function throwException()
    {
        throw new \RuntimeException();
    }
}

throw new \RuntimeException();
// @codingStandardsIgnoreEnd

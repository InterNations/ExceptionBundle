<?php // @codingStandardsIgnoreStart
namespace InterNations\Bundle\ExceptionTestBundle;

use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;
use InterNations\Bundle\ExceptionTestBundle\Exception\BadMethodCallException,
    BadFunctionCallException;
use InterNations\Bundle\ExceptionTestBundle\Exception\LogicException;

class FqException
{
    public function throwRuntimeException()
    {
        throw new RuntimeException();
    }

    public function throwBadMethodCallException()
    {
        throw new BadMethodCallException('Message');
    }

    public function throwFqBadMethodCallException()
    {
        throw new BadMethodCallException('Message');
    }

    public function throwLogicException()
    {
        throw new LogicException('Message', 123, new BadFunctionCallException());
    }

    public function catchException()
    {
        try {
            $this->something();
        } catch (Exception $e) {
        }
    }
}
// @codingStandardsIgnoreEnd

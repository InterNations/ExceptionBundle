<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use BadMethodCallException,
    BadFunctionCallException;
use LogicException;

class FqException
{
    public function throwRuntimeException()
    {
        throw new \RuntimeException();
    }

    public function throwBadMethodCallException()
    {
        throw new BadMethodCallException('Message');
    }

    public function throwFqBadMethodCallException()
    {
        throw new \BadMethodCallException('Message');
    }

    public function throwLogicException()
    {
        throw new LogicException('Message', 123, new BadFunctionCallException());
    }
}

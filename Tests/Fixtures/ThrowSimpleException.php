<?php
namespace InterNations\Bundle\ExceptionTestBundle;

use RuntimeException;
use BadMethodCallException;
use Custom\Exception as CustomException;

function throw_simple_exception()
{
    throw new RuntimeException('Runtime Exception Message');
}

function throw_simple_exception_with_code()
{
    throw new RuntimeException('Runtime Exception Message', 1);
}

function throw_factory()
{
    throw RuntimeExceptionWithFactory::factory();
}

class ThrowSimpleException
{
    public function throwException()
    {
        throw new BadMethodCallException('BadMethodCall Exception Message');
    }

    public function throwExceptionWithCode()
    {
        throw new BadMethodCallException('BadMethodCall Exception Message', 1);
    }

    public function throwExceptionWithPrevious()
    {
        throw new BadMethodCallException('BadMethodCall Exception Message', 2, $previous);
    }

    public function testThrowCustomException()
    {
        throw new CustomException('Custom Exception should be ignored');
    }

    public function testThrowVariable()
    {
        throw new $variable;
    }
}
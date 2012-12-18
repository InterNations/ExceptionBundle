<?php
namespace InterNations\Bundle\ExceptionBundle\Exception;

use BadMethodCallException as BaseBadMethodCallException;

class BadMethodCallException extends BaseBadMethodCallException implements ExceptionInterface
{
}
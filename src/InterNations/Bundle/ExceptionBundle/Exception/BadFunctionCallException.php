<?php
namespace InterNations\Bundle\ExceptionBundle\Exception;

use BadFunctionCallException as BaseBadFunctionCallException;

class BadFunctionCallException extends BaseBadFunctionCallException implements ExceptionInterface
{
}
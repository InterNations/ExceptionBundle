<?php
namespace InterNations\Bundle\ExceptionBundle\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
}
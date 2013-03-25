<?php
namespace InterNations\Bundle\ExceptionBundle\Exception;

use UnexpectedValueException as BaseUnexpectedValueException;

class UnexpectedValueException extends BaseUnexpectedValueException implements ExceptionInterface
{
}

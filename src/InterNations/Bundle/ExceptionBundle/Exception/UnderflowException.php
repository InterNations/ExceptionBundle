<?php
namespace InterNations\Bundle\ExceptionBundle\Exception;

use UnderflowException as BaseUnderflowException;

class UnderflowException extends BaseUnderflowException implements ExceptionInterface
{
}
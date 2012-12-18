<?php
namespace InterNations\Bundle\ExceptionBundle\Exception;

use DomainException as BaseDomainException;

class DomainException extends BaseDomainException implements ExceptionInterface
{
}
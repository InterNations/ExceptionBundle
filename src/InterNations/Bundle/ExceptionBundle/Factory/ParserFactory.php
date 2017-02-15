<?php
namespace InterNations\Bundle\ExceptionBundle\Factory;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser;

final class ParserFactory
{
    /** @return Parser */
    public static function createParser()
    {
        return new Parser\Multiple([new Parser\Php7(new Emulative()), new Parser\Php5(new Emulative())]);
    }
}

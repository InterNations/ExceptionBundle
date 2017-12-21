<?php

function throw_exception()
{
    throw new InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException('Message');
}

throw new InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException('Message');

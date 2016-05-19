<?php // @codingStandardsIgnoreLine

function throw_exception()
{
    throw new RuntimeException('Message');
}

throw new RuntimeException('Message');

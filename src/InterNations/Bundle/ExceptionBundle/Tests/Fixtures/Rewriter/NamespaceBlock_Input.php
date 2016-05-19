<?php
namespace InterNations\Bundle\ExceptionTestBundle { // @codingStandardsIgnoreLine

    use RuntimeException;

    class UseException // @codingStandardsIgnoreLine
    {
        public function throwException()
        {
            throw new RuntimeException();
        }
    }
}

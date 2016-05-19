<?php
namespace InterNations\Bundle\ExceptionTestBundle { // @codingStandardsIgnoreLine

    use InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException;

    class UseException // @codingStandardsIgnoreLine
    {
        public function throwException()
        {
            throw new RuntimeException();
        }
    }
}

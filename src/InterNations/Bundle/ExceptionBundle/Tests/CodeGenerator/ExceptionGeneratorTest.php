<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\CodeGenerator;

use InterNations\Bundle\ExceptionBundle\CodeGenerator\ExceptionGenerator;

class ExceptionGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratingExceptionWithoutMarkerInterface()
    {
        $generator = new ExceptionGenerator('My\Namespace');
        $code = <<<'EOS'
<?php
namespace My\Namespace;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException
{
}

EOS;
        $this->assertSame($code, $generator->generate('RuntimeException'));
    }

    public function testGeneratingExceptionWithMarkerInterfaceInDifferentNamespace()
    {
        $generator = new ExceptionGenerator('My\Namespace', 'Another\Namespace\ExceptionInterface');
        $code = <<<'EOS'
<?php
namespace My\Namespace;

use Another\Namespace\ExceptionInterface;
use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
}

EOS;
        $this->assertSame($code, $generator->generate('RuntimeException'));
    }

    public function testGeneratingExceptionInTheSameNamespace()
    {
        $generator = new ExceptionGenerator('My\Namespace', 'My\Namespace\ExceptionInterface');
        $code = <<<'EOS'
<?php
namespace My\Namespace;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
}

EOS;
        $this->assertSame($code, $generator->generate('RuntimeException'));
    }

    public function testExceptionIsThrownForInvalidExceptionClass_1()
    {
        $generator = new ExceptionGenerator('My\Namespace', 'My\Namespace\ExceptionInterface');
        $this->setExpectedException(
            'RuntimeException',
            'Given exception base class "FooException" is not a child class of "Exception"'
        );
        $generator->generate('FooException');
    }

    public function testExceptionIsThrownForInvalidExceptionClass_2()
    {
        $generator = new ExceptionGenerator('My\Namespace', 'My\Namespace\ExceptionInterface');
        $this->setExpectedException(
            'RuntimeException',
            'Given exception base class "stdClass" is not a child class of "Exception"'
        );
        $generator->generate('stdClass');
    }
}

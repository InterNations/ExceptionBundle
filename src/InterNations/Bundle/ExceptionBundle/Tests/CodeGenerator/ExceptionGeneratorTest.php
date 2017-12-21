<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\CodeGenerator;

use InterNations\Bundle\ExceptionBundle\CodeGenerator\ExceptionGenerator;
use PHPUnit\Framework\TestCase;

class ExceptionGeneratorTest extends TestCase
{
    public function testGeneratingExceptionWithoutMarkerInterface(): void
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
        self::assertSame($code, $generator->generate('RuntimeException'));
    }

    public function testGeneratingExceptionWithMarkerInterfaceInDifferentNamespace(): void
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
        self::assertSame($code, $generator->generate('RuntimeException'));
    }

    public function testGeneratingExceptionInTheSameNamespace(): void
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
        self::assertSame($code, $generator->generate('RuntimeException'));
    }

    public function testExceptionIsThrownForInvalidExceptionClass1(): void
    {
        $generator = new ExceptionGenerator('My\Namespace', 'My\Namespace\ExceptionInterface');
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Given exception base class "FooException" is not a child class of "Exception"');
        $generator->generate('FooException');
    }

    public function testExceptionIsThrownForInvalidExceptionClass2(): void
    {
        $generator = new ExceptionGenerator('My\Namespace', 'My\Namespace\ExceptionInterface');
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Given exception base class "stdClass" is not a child class of "Exception"');
        $generator->generate('stdClass');
    }
}

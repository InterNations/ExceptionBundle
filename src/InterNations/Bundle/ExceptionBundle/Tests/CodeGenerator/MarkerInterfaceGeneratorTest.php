<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\CodeGenerator;

use InterNations\Bundle\ExceptionBundle\CodeGenerator\MarkerInterfaceGenerator;

class MarkerInterfaceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratorMarkerInterface()
    {
        $generator = new MarkerInterfaceGenerator('My\Namespace');
        $code = <<<'EOS'
<?php
namespace My\Namespace;

interface MarkerInterface
{
}

EOS;
        $this->assertSame($code, $generator->generate('MarkerInterface'));
    }
}

<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\CodeGenerator;

use InterNations\Bundle\ExceptionBundle\CodeGenerator\MarkerInterfaceGenerator;
use PHPUnit_Framework_TestCase as TestCase;

class MarkerInterfaceGeneratorTest extends TestCase
{
    public function testGeneratorMarkerInterface()
    {
        $generator = new MarkerInterfaceGenerator('My\Namespace');
        $code = <<<'EOS'
<?php
namespace My\Namespace;

use Exception;

interface MarkerInterface
{
    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return mixed
     */
    public function getCode();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return integer
     */
    public function getLine();

    /**
     * @return array
     */
    public function getTrace();

    /**
     * @return Exception|null
     */
    public function getPrevious();

    /**
     * @return string
     */
    public function getTraceAsString();
}

EOS;
        $this->assertSame($code, $generator->generate('MarkerInterface'));
    }
}

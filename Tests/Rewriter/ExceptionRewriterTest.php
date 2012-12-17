<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\Rewriter;

use InterNations\Bundle\ExceptionBundle\Rewriter\ExceptionRewriter;

class ExceptionRewriterTest extends \PHPUnit_Framework_TestCase
{
    private $rewriter;

    private $file;

    public function setUp()
    {
        $this->rewriter = new ExceptionRewriter('InterNations\Bundle\ExceptionTestBundle');
        $this->file = $this->getMockBuilder('SplFileObject')
            ->setConstructorArgs(['/dev/null'])
            ->getMock();
    }

    public function getRewriteTestFiles()
    {
        $arguments = [];
        foreach (glob(__DIR__ . '/../Fixtures/Rewriter/*_Input.php') as $file) {
            $arguments[] = [$file, strtr($file, ['_Input.php' => '_Output.php'])];
        }

        return $arguments;
    }

    /**
     * @dataProvider getRewriteTestFiles
     */
    public function testRewritingFqExceptions($inputFile, $outputFile)
    {
        $this->rewriter->registerBundleException('InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException');

        $this->file
            ->expects($this->any())
            ->method('fgets')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(file($inputFile)));
        $this->file
            ->expects($this->once())
            ->method('fwrite')
            ->with(file_get_contents($outputFile));

        $this->rewriter->rewrite($this->file);
    }
}

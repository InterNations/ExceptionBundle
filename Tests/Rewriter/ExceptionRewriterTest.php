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
        $this->rewriter->registerBundleException('InterNations\Bundle\ExceptionTestBundle\Exception\LogicException');
        $this->rewriter->registerBundleException('InterNations\Bundle\ExceptionTestBundle\Exception\BadMethodCallException');
        $this->rewriter->registerBundleException('InterNations\Bundle\ExceptionTestBundle\Exception\BadFunctionCallException');

        $lines = file($inputFile);
        $eof = array_fill(0, count($lines), false);
        $eof[] = true;
        $this->file
            ->expects($this->any())
            ->method('eof')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($eof));
        $this->file
            ->expects($this->any())
            ->method('fgets')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($lines));
        $this->file
            ->expects($this->once())
            ->method('seek')
            ->with(0);
        $this->file
            ->expects($this->once())
            ->method('fwrite')
            ->with(file_get_contents($outputFile));

        $this->rewriter->rewrite($this->file);
    }
}

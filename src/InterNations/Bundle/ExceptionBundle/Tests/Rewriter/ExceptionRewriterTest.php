<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\Rewriter;

use InterNations\Bundle\ExceptionBundle\Rewriter\ExceptionRewriter;
use InterNations\Component\Testing\AbstractTestCase;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls as ConsecutiveCallsStub;

/** @large */
class ExceptionRewriterTest extends AbstractTestCase
{
    /**
     * @var ExceptionRewriter
     */
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
            $arguments[$file] = [$file, strtr($file, ['_Input.php' => '_Output.php'])];
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
        $this->rewriter->registerBundleException(
            'InterNations\Bundle\ExceptionTestBundle\Exception\BadMethodCallException'
        );
        $this->rewriter->registerBundleException(
            'InterNations\Bundle\ExceptionTestBundle\Exception\BadFunctionCallException'
        );

        $this->mockFileAccess($inputFile, $outputFile);

        $this->rewriter->rewrite($this->file);
    }

    public function testReportReturned()
    {
        $this->rewriter->registerBundleException('InterNations\Bundle\ExceptionTestBundle\Exception\RuntimeException');
        $this->rewriter->registerBundleException('InterNations\Bundle\ExceptionTestBundle\Exception\LogicException');
        $this->rewriter->registerBundleException(
            'InterNations\Bundle\ExceptionTestBundle\Exception\BadMethodCallException'
        );
        $this->rewriter->registerBundleException(
            'InterNations\Bundle\ExceptionTestBundle\Exception\BadFunctionCallException'
        );

        $this->mockFileAccess(
            __DIR__ . '/../Fixtures/Rewriter/ManyExceptions_Input.php',
            __DIR__ . '/../Fixtures/Rewriter/ManyExceptions_Output.php'
        );

        $report = $this->rewriter->rewrite($this->file);

        $this->assertSame(4, $report->throwStatementsFound);
        $this->assertSame(2, $report->throwStatementsRewritten);

        $this->assertSame(3, $report->useStatementsFound);
        $this->assertSame(2, $report->useStatementsRewritten);
        $this->assertSame(1, $report->useStatementsAdded);

        $this->assertSame(1, $report->catchStatementsFound);
    }

    private function mockFileAccess($inputFile, $outputFile)
    {
        $lines = file($inputFile);
        $eof = array_fill(0, count($lines), false);
        $eof[] = true;
        $this->file
            ->method('eof')
            ->will(new ConsecutiveCallsStub($eof));
        $this->file
            ->method('fgets')
            ->will(new ConsecutiveCallsStub($lines));
        $this->file
            ->expects($this->once())
            ->method('seek')
            ->with(0);
        $this->file
            ->expects($this->once())
            ->method('fwrite')
            ->with(file_get_contents($outputFile));
    }
}

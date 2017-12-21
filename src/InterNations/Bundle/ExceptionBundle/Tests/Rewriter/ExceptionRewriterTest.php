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

    protected function setUp(): void
    {
        $this->rewriter = new ExceptionRewriter('InterNations\Bundle\ExceptionTestBundle');

        $this->file = $this->getMockBuilder('SplFileObject')
            ->setConstructorArgs(['/dev/null'])
            ->getMock();
    }

    /** @return array[] */
    public static function getRewriteTestFiles(): array
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
    public function testRewritingFqExceptions(string $inputFile, string $outputFile): void
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

    public function testReportReturned(): void
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

        self::assertSame(4, $report->throwStatementsFound);
        self::assertSame(2, $report->throwStatementsRewritten);

        self::assertSame(3, $report->useStatementsFound);
        self::assertSame(2, $report->useStatementsRewritten);
        self::assertSame(1, $report->useStatementsAdded);

        self::assertSame(1, $report->catchStatementsFound);
    }

    private function mockFileAccess(string $inputFile, string $outputFile): void
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
            ->expects(self::once())
            ->method('seek')
            ->with(0);
        $this->file
            ->expects(self::once())
            ->method('fwrite')
            ->with(file_get_contents($outputFile));
    }
}

<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\Visitor;

use InterNations\Bundle\ExceptionBundle\Factory\ParserFactory;
use InterNations\Bundle\ExceptionBundle\Visitor\ExceptionVisitor;
use InterNations\Component\Testing\AbstractTestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver as NameResolverVisitor;
use PhpParser\Node\Stmt\Throw_ as ThrowStatement;
use PhpParser\Node\Expr\New_ as NewExpression;
use PhpParser\Node\Expr\StaticCall as StaticCallExpression;
use PhpParser\Parser;

class ExceptionVisitorTest extends AbstractTestCase
{
    /** @var ExceptionVisitor */
    private $visitor;

    /** @var Parser */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    protected function setUp(): void
    {
        $this->visitor = new ExceptionVisitor();
        $this->parser = ParserFactory::createParser();
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolverVisitor());
        $this->traverser->addVisitor($this->visitor);
    }

    public function testVisitingThrowStatements(): void
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');

        self::assertCount(8, $this->visitor->getThrowStatements());

        $asserted = true;

        foreach ($this->visitor->getThrowStatements() as $stmt) {
            self::assertInstanceOf(ThrowStatement::class, $stmt);
            self::assertSame(
                'InterNations\Bundle\ExceptionTestBundle',
                $stmt->getAttribute('namespace')->name->toString('\\')
            );
            $asserted = true;
        }
        self::assertTrue($asserted);
    }

    public function testFilterThrowStatementsByExpression(): void
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        self::assertCount(7, $this->visitor->getThrowStatements([NewExpression::class]));
    }

    public function testFilterThrowStatementsByGlobalNamespaceAndExpression(): void
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        self::assertCount(5, $this->visitor->getThrowStatements([NewExpression::class], '\\'));
    }

    public function testFilterThrowStatementsBySpecificNamespaceAndExpression(): void
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        self::assertCount(1, $this->visitor->getThrowStatements([NewExpression::class], 'Custom'));
        self::assertCount(
            1,
            $this->visitor->getThrowStatements([StaticCallExpression::class, NewExpression::class], '\\Custom')
        );
    }

    public function testGetUseStatements(): void
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        self::assertCount(3, $this->visitor->getUseStatements());
    }

    private function traverseFile(string $file): void
    {
        $statements = $this->parser->parse(file_get_contents($file));
        $this->traverser->traverse($statements);
    }
}

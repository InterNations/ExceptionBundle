<?php
namespace InterNations\Bundle\ExceptionBundle\Tests\Visitor;

use InterNations\Bundle\ExceptionBundle\Visitor\ExceptionVisitor;
use InterNations\Component\Testing\AbstractTestCase;
use PHPParser_Parser as Parser;
use PHPParser_Lexer as Lexer;
use PHPParser_NodeTraverser as NodeTraverser;
use PHPParser_NodeVisitor_NameResolver as NameResolverVisitor;

class ExceptionVisitorTest extends AbstractTestCase
{
    /**
     * @var ExceptionVisitor
     */
    private $visitor;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $traverser;

    public function setUp()
    {
        $this->visitor = new ExceptionVisitor();
        $this->parser = new Parser(new Lexer());
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolverVisitor());
        $this->traverser->addVisitor($this->visitor);
    }

    public function testVisitingThrowStatements()
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');

        $this->assertCount(8, $this->visitor->getThrowStatements());

        $asserted = true;

        foreach ($this->visitor->getThrowStatements() as $stmt) {
            $this->assertInstanceOf('PHPParser_Node_Stmt_Throw', $stmt);
            $this->assertSame(
                'InterNations\\Bundle\\ExceptionTestBundle',
                $stmt->getAttribute('namespace')->name->toString('\\')
            );
            $asserted = true;
        }
        $this->assertTrue($asserted);
    }

    public function testFilterThrowStatementsByExpression()
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        $this->assertCount(7, $this->visitor->getThrowStatements(['PHPParser_Node_Expr_New']));
    }

    public function testFilterThrowStatementsByGlobalNamespaceAndExpression()
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        $this->assertCount(5, $this->visitor->getThrowStatements(['PHPParser_Node_Expr_New'], '\\'));
    }

    public function testFilterThrowStatementsBySpecificNamespaceAndExpression()
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        $this->assertCount(1, $this->visitor->getThrowStatements(['PHPParser_Node_Expr_New'], 'Custom'));
        $this->assertCount(
            1,
            $this->visitor->getThrowStatements(
                ['PHPParser_Node_Expr_StaticCall', 'PHPParser_Node_Expr_New'],
                '\\Custom'
            )
        );
    }

    public function testGetUseStatements()
    {
        $this->traverseFile(__DIR__ . '/../Fixtures/ThrowSimpleException.php');
        $this->assertCount(3, $this->visitor->getUseStatements());
    }

    private function traverseFile($file)
    {
        $statements = $this->parser->parse(file_get_contents($file));
        $this->traverser->traverse($statements);
    }
}

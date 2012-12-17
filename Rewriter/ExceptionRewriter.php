<?php
namespace InterNations\Bundle\ExceptionBundle\Rewriter;

use SplFileObject;
use PHPParser_Parser as Parser;
use PHPParser_Lexer as Lexer;
use PHPParser_NodeTraverser as NodeTraverser;
use PHPParser_NodeVisitor_NameResolver as NameResolverVisitor;
use PHPParser_NodeAbstract as AbstractNode;
use InterNations\Bundle\ExceptionBundle\Visitor\ExceptionVisitor;

class ExceptionRewriter
{
    private $bundleExceptions = [];

    private $bundleNamespace;

    public function __construct($bundleNamespace)
    {
        $this->bundleNamespace = $bundleNamespace;
    }


    public function registerBundleException($exceptionClassName)
    {
        $this->bundleExceptions[] = $exceptionClassName;
    }

    public function rewrite(SplFileObject $file)
    {
        $lines = [];
        while ($line = $file->fgets()) {
            $lines[] = $line;
        }

        $parser = new Parser(new Lexer());
        $stmts = $parser->parse(join('', $lines));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolverVisitor());
        $exceptionVisitor = new ExceptionVisitor();
        $traverser->addVisitor($exceptionVisitor);
        $traverser->traverse($stmts);

        foreach ($exceptionVisitor->getThrowStatements('PHPParser_Node_Expr_New', '\\') as $throwStmt) {

            $exceptionClassName = $throwStmt->expr->class->parts[0];

            $useFound = false;
            foreach ($exceptionVisitor->getUseStatements() as $useStmt) {
                foreach ($useStmt->uses as $usageStmt) {
                    if ($usageStmt->name->toString() === $exceptionClassName) {
                        $useFound = true;
                    }
                }
            }

            if (!$useFound) {
                $usagePosition = $this->getArrayPosition($throwStmt->expr->class);
                $lines[$usagePosition] = preg_replace($this->getRegex($exceptionClassName), $exceptionClassName, $lines[$usagePosition]);

                $namespacePosition = $this->getArrayPosition($throwStmt->getAttribute('namespace'));
                $lines[$namespacePosition] .= sprintf("\nuse %s;\n", $this->prependNamespace($exceptionClassName));
            } else {
                $usagePosition = $this->getArrayPosition($usageStmt->name);

                $lines[$usagePosition] = preg_replace(
                    $this->getRegex($usageStmt->name->toString()),
                    $this->prependNamespace($exceptionClassName),
                    $lines[$usagePosition],
                    1
                );
            }
        }

        $file->fwrite(join('', $lines));
    }

    protected function getBundleExceptionNamespace()
    {
        return $this->bundleNamespace . '\\' . 'Exception';
    }

    protected function prependNamespace($className)
    {
        return sprintf('%s\\%s', $this->getBundleExceptionNamespace(), $className);
    }

    protected function getRegex($name)
    {
        return '/\\\\?' . preg_quote($name, '/') . '/';
    }

    protected function getArrayPosition(AbstractNode $node)
    {
        $startLine = $node->getAttribute('startLine');
        $endLine = $node->getAttribute('endLine');

        assert($startLine === $endLine);
        assert($startLine - 1 > 0);

        return $startLine - 1;
    }
}
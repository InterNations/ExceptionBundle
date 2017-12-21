<?php
namespace InterNations\Bundle\ExceptionBundle\Rewriter;

use InterNations\Bundle\ExceptionBundle\Factory\ParserFactory;
use PhpParser\Node\Expr\StaticCall as StaticCallExpression;
use PhpParser\Parser;
use SplFileObject;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver as NameResolverVisitor;
use PhpParser\NodeAbstract as AbstractNode;
use PhpParser\Node\Stmt\Use_ as UseStatement;
use PhpParser\Node\Expr\New_ as NewExpression;
use InterNations\Bundle\ExceptionBundle\Visitor\ExceptionVisitor;
use InterNations\Bundle\ExceptionBundle\Value\Report;

class ExceptionRewriter
{
    /** @var array */
    private $bundleExceptions = [];

    /** @var array */
    private $specializedExceptions = [];

    /** @var string */
    private $bundleNamespace;

    /** @var Parser */
    private $parser;

    public function __construct(string $bundleNamespace)
    {
        $this->bundleNamespace = $bundleNamespace;
        $this->parser = ParserFactory::createParser();
    }

    public function registerBundleException(string $exceptionClassName): void
    {
        $this->bundleExceptions[] = $exceptionClassName;
        $this->specializedExceptions[] = substr($exceptionClassName, strrpos($exceptionClassName, '\\') + 1);
    }

    public function rewrite(SplFileObject $file): Report
    {
        $lines = [];
        $buffer = '';

        while (!$file->eof()) {
            $buffer .= $lines[] = $file->fgets();
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolverVisitor());
        $exceptionVisitor = new ExceptionVisitor();
        $traverser->addVisitor($exceptionVisitor);

        $traverser->traverse($this->parser->parse($buffer));

        $report = new Report();
        $report->catchStatementsFound = count($exceptionVisitor->getCatchStatements());
        $report->throwStatementsFound = count($exceptionVisitor->getThrowStatements());

        foreach ($exceptionVisitor->getUseStatements() as $useStatement) {
            $report->useStatementsFound += count($useStatement->uses);
        }

        $useStatementsProcessed = [];
        $throwStatements = $exceptionVisitor->getThrowStatements(
            [NewExpression::class, StaticCallExpression::class],
            '\\'
        );

        foreach ($throwStatements as $throwStmt) {

            $exceptionClassName = $throwStmt->expr->class->toString();

            if (!$this->hasSpecializedException($exceptionClassName)) {
                continue;
            }

            $useStatementFound = false;
            /** @var $useStatement UseStatement */
            foreach ($exceptionVisitor->getUseStatements() as $useStatement) {
                foreach ($useStatement->uses as $usageStmt) {
                    if ($usageStmt->name->toString() === $exceptionClassName) {
                        $useStatementFound = true;
                        break 2;
                    }
                }
            }

            $namespaceStmt = $throwStmt->getAttribute('namespace');
            $throwPosition = $this->getArrayPosition($throwStmt->expr->class);
            $replacement = $namespaceStmt ? $exceptionClassName : $this->prependNamespace($exceptionClassName);
            $originalThrowLine = $lines[$throwPosition];
            $lines[$throwPosition] = preg_replace(
                $this->getRegex($exceptionClassName),
                $replacement,
                $lines[$throwPosition]
            );

            if ($originalThrowLine != $lines[$throwPosition]) {
                $report->throwStatementsRewritten++;
            }

            if (!$useStatementFound && $namespaceStmt && !in_array($exceptionClassName, $useStatementsProcessed)) {

                $namespacePosition = $this->getArrayPosition($throwStmt->getAttribute('namespace'));
                $lines[$namespacePosition] .= sprintf("\nuse %s;", $this->prependNamespace($exceptionClassName));
                $useStatementsProcessed[] = $exceptionClassName;
                $report->useStatementsAdded++;

            } elseif ($useStatementFound && !in_array($exceptionClassName, $useStatementsProcessed)) {

                $usagePosition = $this->getArrayPosition($usageStmt->name);
                $lines[$usagePosition] = preg_replace(
                    $this->getRegex($usageStmt->name->toString()),
                    $this->prependNamespace($exceptionClassName),
                    $lines[$usagePosition],
                    1
                );

                $report->useStatementsRewritten++;

                $useStatementsProcessed[] = $exceptionClassName;

            }
        }

        $file->seek(0);
        $file->fwrite(implode('', $lines));

        return $report;
    }

    protected function getBundleExceptionNamespace(): string
    {
        return $this->bundleNamespace . '\\' . 'Exception';
    }

    protected function prependNamespace(string $className): string
    {
        return sprintf('%s\\%s', $this->getBundleExceptionNamespace(), $className);
    }

    protected function getRegex(string $name): string
    {
        return '/\\\\?' . preg_quote($name, '/') . '/';
    }

    protected function getArrayPosition(AbstractNode $node): int
    {
        $startLine = $node->getAttribute('startLine');
        $endLine = $node->getAttribute('endLine');

        assert($startLine === $endLine);
        assert($startLine - 1 > 0);

        return $startLine - 1;
    }

    protected function hasSpecializedException(string $exceptionClassName): bool
    {
        return in_array($exceptionClassName, $this->specializedExceptions, true);
    }
}

<?php
namespace InterNations\Bundle\ExceptionBundle\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract as AbstractNodeVisitor;
use PhpParser\Node\Stmt\Throw_ as ThrowStatement;
use PhpParser\Node\Stmt\Catch_ as CatchStatement;
use PhpParser\Node\Stmt\Use_ as UseStatement;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStatement;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;

class ExceptionVisitor extends AbstractNodeVisitor
{
    /** @var UseStatement[] */
    private $useStatements = [];

    /** @var ThrowStatement[] */
    private $throwStatements = [];

    /** @var CatchStatement[] */
    private $catchStatements = [];

    /** @var NamespaceStatement */
    private $currentNamespace;

    public function enterNode(Node $node)
    {
        if ($node instanceof NamespaceStatement) {
            $this->currentNamespace = $node;
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ThrowStatement) {
            $this->throwStatements[] = $node;

            if ($this->currentNamespace) {
                $node->setAttribute('namespace', $this->currentNamespace);
            }
        } elseif ($node instanceof UseStatement) {
            $this->useStatements[] = $node;
        } elseif ($node instanceof CatchStatement) {
            $this->catchStatements[] = $node;
        }
    }

    public function getThrowStatements(array $expressionTypes = null, $namespace = null)
    {
        $throwStatements = $this->throwStatements;

        if ($expressionTypes !== null) {
            $throwStatements = array_filter(
                $throwStatements,
                static function ($stmt) use ($expressionTypes) {
                    foreach ($expressionTypes as $expressionType) {
                        if ($stmt->expr instanceof $expressionType) {
                            return true;
                        }
                    }

                    return false;
                }
            );
        }

        if ($namespace !== null) {
            $throwStatements = array_filter(
                $throwStatements,
                function ($stmt) use ($namespace) {
                    return $this->isInNamespace($namespace, $stmt->expr->class);
                }
            );
        }

        return $throwStatements;
    }

    public function getUseStatements()
    {
        return $this->useStatements;
    }

    public function getCatchStatements()
    {
        return $this->catchStatements;
    }

    private function isInNamespace($namespace, Node $node)
    {
        if (!$node instanceof FullyQualifiedName) {
            return false;
        }

        // Special case global namespace
        if ($namespace === '\\') {
            return strpos($node->toString(), '\\') === false;
        }

        $namespace = trim($namespace, '\\');

        return strpos($node->toString(), $namespace) === 0;
    }
}

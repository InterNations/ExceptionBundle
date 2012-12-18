<?php
namespace InterNations\Bundle\ExceptionBundle\Visitor;

use PHPParser_NodeVisitorAbstract as AbstractNodeVisitor;
use PHPParser_Node as Node;
use PHPParser_Node_Stmt_Throw as ThrowStmt;
use PHPParser_Node_Stmt_Use as UseStmt;
use PHPParser_Node_Stmt_Namespace as NamespaceStmt;
use PHPParser_Node_Name_FullyQualified as FullyQualifiedName;

class ExceptionVisitor extends AbstractNodeVisitor
{
    /**
     * @var UseStmt[]
     */
    private $useStatements = [];

    /**
     * @var ThrowStmt[]
     */
    private $throwStatements = [];

    /**
     * @var NamespaceStmt
     */
    private $currentNamespace;

    public function enterNode(Node $node)
    {
        if ($node instanceof NamespaceStmt) {
            $this->currentNamespace = $node;
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ThrowStmt) {
            $this->throwStatements[] = $node;
            if ($this->currentNamespace) {
                $node->setAttribute('namespace', $this->currentNamespace);
            }
        } elseif ($node instanceof UseStmt) {
            $this->useStatements[] = $node;
        }
    }

    public function getThrowStatements(array $expressionTypes = null, $namespace = null)
    {
        $throwStatements = $this->throwStatements;

        if ($expressionTypes !== null) {
            $throwStatements = array_filter(
                $throwStatements,
                function ($stmt) use ($expressionTypes) {
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

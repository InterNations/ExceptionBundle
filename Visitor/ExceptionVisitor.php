<?php
namespace InterNations\Bundle\ExceptionBundle\Visitor;

use PHPParser_NodeVisitorAbstract as AbstractNodeVisitor;
use PHPParser_Node as Node;
use PHPParser_Node_Stmt_Throw as ThrowStmt;
use PHPParser_Node_Stmt_Use as UseStmt;
use PHPParser_Node_Stmt_Namespace as NamespaceStmt;

class ExceptionVisitor extends AbstractNodeVisitor
{
    private $useStatements = [];

    private $throwStatements = [];

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

    public function getThrowStatements($expressionType = null, $namespace = null)
    {
        $throwStatements = $this->throwStatements;

        if ($expressionType !== null) {
            foreach ($throwStatements as $key => $stmt) {
                if (!$stmt->expr instanceof $expressionType) {
                    unset($throwStatements[$key]);
                }
            }
        }

        if ($namespace !== null) {
            foreach ($throwStatements as $key => $stmt) {
                if (!$this->isInNamespace($namespace, $stmt->expr->class->parts)) {
                    unset($throwStatements[$key]);
                }
            }
        }

        return $throwStatements;
    }

    public function getUseStatements()
    {
        return $this->useStatements;
    }

    private function isInNamespace($namespace, array $parts)
    {
        // Special case global namespace
        if ($namespace === '\\') {
            return count($parts) === 1;
        }

        $namespace = trim($namespace, '\\');
        return strpos(join('\\', $parts), $namespace) === 0;
    }
}

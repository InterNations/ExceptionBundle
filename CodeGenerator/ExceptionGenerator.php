<?php
namespace InterNations\Bundle\ExceptionBundle\CodeGenerator;

use ReflectionClass;
use InterNations\Bundle\ExceptionBundle\Exception\RuntimeException;
use ReflectionException;

class ExceptionGenerator
{
    private $namespace;

    private $markerInterface;

    public function __construct($namespace, $exceptionInterfaceName = null)
    {
        $this->namespace = $namespace;
        $this->markerInterface = $exceptionInterfaceName;
    }

    public function generate($exceptionClass, $parentExceptionClass = null)
    {
        $parentExceptionClass = $parentExceptionClass ?: $exceptionClass;

        try {
            $class = new ReflectionClass($parentExceptionClass);
            $isSubclass = $class->isSubclassOf('Exception');
        } catch (ReflectionException $e) {
            try {
                $class = new ReflectionClass($this->namespace . '\\' . $parentExceptionClass);
                $isSubclass = $class->isSubclassOf('Exception');
            } catch (ReflectionException $e) {
                $isSubclass = false;
            }
        }

        if (!$isSubclass) {
            throw new RuntimeException(
                sprintf('Given exception base class "%s" is not a child class of "Exception"', $parentExceptionClass)
            );
        }

        $code = array();
        $code[] = '<?php';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';
        if ($this->markerInterface && $this->getNamespace($this->markerInterface) !== $this->namespace) {
            $code[] = 'use ' . $this->markerInterface . ';';
        }
        if ($parentExceptionClass === $exceptionClass) {
            $code[] = 'use ' . $exceptionClass . ' as ' . $this->getBaseAlias($exceptionClass, $parentExceptionClass) . ';';
        }
        $code[] = '';
        $code[] = 'class ' . $exceptionClass . ' extends ' . $this->getBaseAlias($parentExceptionClass, $exceptionClass) . ($this->markerInterface ? ' implements ' . $this->getShortName($this->markerInterface) : '');
        $code[] = '{';
        $code[] = '}';

        return join("\n", $code);
    }

    private function getShortName($name)
    {
        return substr($name, strrpos($name, '\\') + 1);
    }

    private function getNamespace($namespace)
    {
        return substr($namespace, 0, strrpos($namespace, '\\'));
    }

    protected function getBaseAlias($parentExceptionClass, $exceptionClass)
    {
        if ($parentExceptionClass !== $exceptionClass) {
            return $parentExceptionClass;
        }

        return 'Base' . $parentExceptionClass;
    }
}
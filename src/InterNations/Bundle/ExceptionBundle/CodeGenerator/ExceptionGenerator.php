<?php
namespace InterNations\Bundle\ExceptionBundle\CodeGenerator;

use ReflectionClass;
use InterNations\Bundle\ExceptionBundle\Exception\RuntimeException;
use ReflectionException;

class ExceptionGenerator
{
    private $namespace;
    private $markerInterface;

    public function __construct(string $namespace, ?string $exceptionInterfaceName = null)
    {
        $this->namespace = $namespace;
        $this->markerInterface = $exceptionInterfaceName;
    }

    public function generate(string $exceptionClass, ?string $parentExceptionClass = null): string
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

        $code = [];
        $code[] = '<?php';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';

        if ($this->markerInterface && $this->getNamespace($this->markerInterface) !== $this->namespace) {
            $code[] = 'use ' . $this->markerInterface . ';';
        }

        if ($parentExceptionClass === $exceptionClass) {
            $code[] = 'use ' . $exceptionClass
                . ' as ' . $this->getBaseAlias($exceptionClass, $parentExceptionClass) . ';';
        }

        $code[] = '';
        $code[] = 'class ' . $exceptionClass
            . ' extends ' . $this->getBaseAlias($parentExceptionClass, $exceptionClass)
            . ($this->markerInterface ? ' implements ' . $this->getShortName($this->markerInterface) : '');
        $code[] = '{';
        $code[] = '}';
        $code[] = '';

        return implode("\n", $code);
    }

    private function getShortName(string $name): string
    {
        return substr($name, strrpos($name, '\\') + 1);
    }

    private function getNamespace(string $namespace): string
    {
        return substr($namespace, 0, strrpos($namespace, '\\'));
    }

    protected function getBaseAlias(string $parentExceptionClass, string $exceptionClass): string
    {
        if ($parentExceptionClass !== $exceptionClass) {
            return $parentExceptionClass;
        }

        return 'Base' . $parentExceptionClass;
    }
}

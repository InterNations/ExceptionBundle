<?php
namespace InterNations\Bundle\ExceptionBundle\CodeGenerator;

class MarkerInterfaceGenerator
{
    private $namespace;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    public function generate($interface)
    {
        $code = [];
        $code[] = '<?php';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';
        $code[] = 'interface ' . $interface;
        $code[] = '{';
        $code[] = '}';

        return implode("\n", $code);
    }
}

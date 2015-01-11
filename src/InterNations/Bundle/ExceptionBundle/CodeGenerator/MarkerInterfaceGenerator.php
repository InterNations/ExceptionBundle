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
        $code[] = 'use Exception;';
        $code[] = '';
        $code[] = 'interface ' . $interface;
        $code[] = '{';
        static::declareMethod($code, 'getMessage', 'string');
        $code[] = '';
        static::declareMethod($code, 'getCode', 'mixed');
        $code[] = '';
        static::declareMethod($code, 'getFile', 'string');
        $code[] = '';
        static::declareMethod($code, 'getLine', 'integer');
        $code[] = '';
        static::declareMethod($code, 'getTrace', 'array');
        $code[] = '';
        static::declareMethod($code, 'getPrevious', 'Exception|null');
        $code[] = '';
        static::declareMethod($code, 'getTraceAsString', 'string');
        $code[] = '}';
        $code[] = '';

        return implode("\n", $code);
    }

    private static function declareMethod(array &$code, $method, $returnType)
    {
        $code[] = '    /**';
        $code[] = '     * @return ' . $returnType;
        $code[] = '     */';
        $code[] = '    public function ' . $method . '();';
    }
}

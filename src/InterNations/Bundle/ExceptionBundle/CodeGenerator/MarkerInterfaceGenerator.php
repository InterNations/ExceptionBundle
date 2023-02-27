<?php
namespace InterNations\Bundle\ExceptionBundle\CodeGenerator;

class MarkerInterfaceGenerator
{
    private $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function generate(string $interface): string
    {
        return self::linesToString(
            [
                '<?php',
                'namespace ' . $this->namespace . ';',
                '',
                'use Exception;',
                '',
                'interface ' . $interface,
                '{',
                static::declareMethod('getMessage', 'string'),
                '',
                static::declareMethod('getCode', 'mixed'),
                '',
                static::declareMethod('getFile', 'string'),
                '',
                static::declareMethod('getLine', 'integer'),
                '',
                static::declareMethod('getTrace', 'array'),
                '',
                static::declareMethod('getPrevious', 'Exception|null'),
                '',
                static::declareMethod('getTraceAsString', 'string'),
                '}',
                '',
            ]
        );
    }

    /** @return string[] */
    private static function declareMethod(string $method, string $returnType): array
    {
        return [
            '    /**',
            '     * @return ' . $returnType,
            '     */',
            '    public function ' . $method . '();',
        ];
    }

    /** @param string[] $lines */
    private static function linesToString(array $lines): string
    {
        return implode(
            "\n",
            array_reduce(
                $lines,
                /**
                 * @param string[] $carry
                 * @param string|array $value
                 * @return string[]
                 */
                static function (array $carry, $value): array {
                    return array_merge($carry, is_array($value) ? $value : [$value]);
                },
                []
            )
        );
    }
}

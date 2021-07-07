<?php
/**
 * Определение установки выражения со свойствами контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\Definitions\Traits\ResolveSubDefinitionTrait;
use Evas\Di\DiException;

class Expression implements DefinitionInterface
{
    /** @var string выражение */
    public $expression;

    /**
     * Конструктор.
     * @param string выражение
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Разрешение определения.
     * @param Container di контейнер
     * @return mixed результат
     * @throws DiException
     */
    public function resolve(Container &$c)
    {
        $callback = function (array $matches) use (&$c) {
            $sub = $matches[1];
            $result = $c->get($sub);
            if (null === $result) {
                throw new DiException("Not found entry for \"$sub\"");
            }
            return $result;
        };
        $result = preg_replace_callback(
            '/\{([^\{\}]+)\}/', $callback, $this->expression
        );
        if (null === $result) {
            throw new DiException("Error parsing the string definition: $this->expression");
        }
        return $result;
    }

    /**
     * Проверка возможности разрешения определения.
     * @param Container di-контейнер
     * @return bool
     */
    public function canResolve(Container &$c): bool
    {
        return true;
    }
}

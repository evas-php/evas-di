<?php
/**
 * Определение свойства связи со свойством контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;

class Reference implements DefinitionInterface
{
    /** @var string имя ссылаемого свойства */
    public $name;

    /**
     * Конструктор.
     * @param string имя ссылаемого свойства
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Разрешение определения.
     * @param Container di-контейнер
     * @return mixed результат
     */
    public function resolve(Container &$c)
    {
        return $c->get($this->name);
    }

    /**
     * Проверка возможности разрешения определения.
     * @param Container di-контейнер
     * @return bool
     */
    public function canResolve(Container &$c): bool
    {
        return $c->has($this->name);
    }
}

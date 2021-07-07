<?php
/**
 * Определение фабрики создание объекта свойства контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\Definitions\Traits\ResolveSubDefinitionTrait;
use Evas\Di\DiException;

class Factory implements DefinitionInterface
{
    /** @var callable функция фабрики */
    public $callback;

    /**
     * Конструктор.
     * @param callable функция фабрики
     */
    public function __construct(callable $callback)
    {
        $this->callback = &$callback;
    }

    /**
     * Разрешение определения.
     * @param Container di контейнер
     * @return mixed результат
     * @throws DiException
     */
    public function resolve(Container &$c)
    {
        // ONCE ?
        $callback = $this->callback->bindTo($c);
        return $callback();
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

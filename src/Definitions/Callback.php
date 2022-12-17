<?php
/**
 * Определение вызова свойства-функции контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\Definitions\Traits\ResolveSubDefinitionTrait;
use Evas\Di\DiException;

class Callback implements DefinitionInterface
{
    /** @var callable коллбек */
    public $callback;

    /**
     * Конструктор.
     * @param callable коллбек
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
        $callback = $this->callback;
        return $callback($c);
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

<?php
/**
 * Интерфейс определения свойства контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;

interface DefinitionInterface
{
    /**
     * Разрешение определения.
     * @param Container di-контейнер
     * @return mixed результат
     */
    public function resolve(Container &$c);

    /**
     * Проверка возможности разрешения определения.
     * @param Container di-контейнер
     * @return bool
     */
    public function canResolve(Container &$c): bool;
}

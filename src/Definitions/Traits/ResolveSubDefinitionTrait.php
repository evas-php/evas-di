<?php
/**
 * Трейт разрешения вложенных определений.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions\Traits;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;

trait ResolveSubDefinitionTrait
{
    /**
     * Возврат оригинального значения или решения, если это определение.
     * @param mixed значение
     * @param Container di контейнер
     * @return mixed оригинальное или решённое значение
     */
    protected function resolveOrOriginal($value, Container &$c)
    {
        return ($value instanceof DefinitionInterface)
            ? $value->resolve($c) : $value;
    }
}
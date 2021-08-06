<?php
/**
 * Функции для удобной сборки конфига di.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di;

use Evas\Di\Definitions\BindStatic;
use Evas\Di\Definitions\Callback;
use Evas\Di\Definitions\CreateObject;
use Evas\Di\Definitions\Expression;
use Evas\Di\Definitions\IncludeFile;
use Evas\Di\Definitions\Reference;

if (! function_exists('create')) {
    function create(string $className, array $args = null, callable $callback = null) {
        return new CreateObject($className, $args, $callback);
    }
}
if (! function_exists('createOnce')) {
    function createOnce(string $className, array $args = null, callable $callback = null) {
        return (new CreateObject($className, $args, $callback))->once();
    }
}
if (! function_exists('includeFile')) {
    function includeFile($filename, array $args = null) {
        return new IncludeFile($filename, $args);
    }
}
if (! function_exists('includeFileOnce')) {
    function includeFileOnce($filename, array $args = null) {
        return (new IncludeFile($filename, $args))->once();
    }
}
if (! function_exists('bindStatic')) {
    function bindStatic(string $className, callable $callback = null) {
        return (new BindStatic($className, $callback));
    }
}
if (! function_exists('string')) {
    function exp(string $expression) {
        return new Expression($expression);
    }
}
if (! function_exists('call')) {
    function call(callable $callback) {
        return new Callback($callback);
    }
}
if (! function_exists('get')) {
    function get(string $name) {
        return new Reference($name);
    }
}

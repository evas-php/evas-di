<?php
/**
 * Статический Di контейнер.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di;

use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\DiException;

include_once __DIR__ . '/functions.php';

class StaticContainer
{
    /** @var StaticContainer единственный экземпляр контейнера */
    protected static $instance;

    /**
     * Получение единственного экземпляра контейнера.
     * @return self
     */
    public static function instance(): StaticContainer
    {
        if (empty(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Установка свойства.
     * @param string имя свойства
     * @param mixed значение свойства
     * @return self
     */
    public static function set(string $name, $value): StaticContainer
    {
        $instance = static::instance();
        if (is_callable($value)) {
            $value = $value->bindTo($instance);
        }
        $instance->$name = &$value;
        return $instance;
    }

    /**
     * Удаление свойства.
     * @param string имя свойства
     * @return self
     */
    public static function unset(string $name): StaticContainer
    {
        unset(static::instance()->$name);
        return static::$instance;
    }

    /**
     * Проверка наличия свойства.
     * @param string имя свойства
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(static::instance()->$name);
    }

    /**
     * Возврат оригинального значения или решения, если это определение.
     * @param mixed значение
     * @return mixed оригинальное или решённое значение
     * @throws DiException
     */
    protected static function resolveOrOriginal($value)
    {
        $instance = static::instance();
        try {
            return $value instanceof DefinitionInterface
            ? $value->resolve($instance) : $value;
        } catch (\Exception $e) {
            throw new DiException("Error with entry \"$name\" resolve: " . $e->getMessage());
        }
    }

    /**
     * Получение свойства.
     * @param string имя свойства
     * @param mixed значение по умолчанию, если нет свойства
     * @param bool установить ли значение по умолчанию, если нет свойства
     * @return mixed значение
     * @throws DiException
     */
    public static function get(string $name, $default = null, bool $setDefaultIfNotHas = false)
    {
        if (!static::has($name)) {
            if (func_num_args() > 1) {
                if (true === $setDefaultIfNotHas) {
                    static::set($name, $default);
                }
                return static::resolveOrOriginal($default);
            }
            return null;
            // throw new DiException("Not found entry for \"$name\"");
        }
        $value = static::instance()->$name;
        return static::resolveOrOriginal($value);
    }

    // Magic

    public function __set(string $name, $value)
    {
        return static::set($name, $value);
    }

    public function __unset(string $name)
    {
        return static::unset($name);
    }

    public function __isset(string $name)
    {
        return static::has($name);
    }

    public function __get(string $name)
    {
        return static::get($name);
    }
}

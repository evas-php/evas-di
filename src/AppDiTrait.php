<?php
/**
 * @package evas-php\evas-di
 */
namespace Evas\Di;

use Evas\Di\Container;

/**
 * Константы для свойств.
 */
if (!defined('EVAS_DI_CLASS')) define('EVAS_DI_CLASS', Container::class);

/**
 * Расширение Di-контейнера приложения.
 * @author Egor Vasyakin <egor@evas-php.com>
 * @since 1.1
 */
trait AppDiTrait
{
    /**
     * @var string класс Di-контейнера
     */
    protected static $diClass = EVAS_DI_CLASS;

    /**
     * @var Container Di-контейнер приложения
     */
    protected static $di;

    /**
     * Установка имени класса Di-контейнера.
     * @param string
     * @return self
     */
    public static function setDiClass(string $diClass): object
    {
        static::$diClass = $diClass;
        return static::instance();
    }

    /**
     * Установка di-контейнера.
     * @param Container
     * @return self
     */
    public static function setDi(Container $di): object
    {
        static::$di = $di;
        return static::instance();
    }

    /**
     * Инициализация di-контейнера.
     * @param array|null параметры di-контейнера
     * @return Container
     */
    public static function initDi(array $vars = null): Container
    {
        static::$di = new static::$diClass($vars);
        return static::$di;
    }

    /**
     * Получение di-контейнера.
     * @param Container|null di-контейнер для установки/переустановки
     * @return Container
     */
    public static function di(Container $di = null): Container
    {
        if (null !== $di) static::setDi($di); 
        if (null === static::$di) static::initDi();
        return static::$di;
    }

    /**
     * Установка параметра или параметров контейнера.
     * @param string|array|object имя параметра или массив/объект параметров
     * @param mixed|null значение параметра или null
     * @return self
     */
    public static function set($name, $value = null)
    {
        static::di()->set($name, $value);
        return static::instance();
    }

    /**
     * Удаление параметра или параметров контейнера.
     * @param string|array имя параметра или массив имен параметров
     * @return self
     */
    public static function unset($name)
    {
        static::di()->unset($name);
        return static::instance();
    }

    /**
     * Проверка наличия параметра или параметров контейнера.
     * @param string|array имя параметра или массив имен парамтеров
     * @param mixed значение, если параметр найден
     * @param mixed значение, если параметр не найден
     * @return mixed
     */
    public static function has(string $name, $defaultIfHas = true, $defaultIfNotHas = false)
    {
        return static::di()->has($name, $defaultIfHas, $defaultIfNotHas);
    }

    /**
     * Получение параметра или массива параметров контейнера.
     * @param string|array|object имя параметра 
     *                            или массив имен параметров 
     *                            или объект имен со значениями по умолчанию
     * @param mixed|null значение по умолчанию
     * @return mixed|array of mixed
     */
    public static function get($name, $default = null)
    {
        return static::di()->get($name, $default);
    }

    /**
     * Вызов параметра-метода контейнера.
     * @param string имя параметра
     * @param array массив аргументов
     * @return mixed результат выполнения метода
     */
    public static function call(string $name, array $arguments = [])
    {
        return static::call($name, $arguments);
    }
}

<?php
/**
 * Определение создание объекта свойства контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\Definitions\Traits\ResolveSubDefinitionTrait;
use Evas\Di\DiException;

class CreateObject implements DefinitionInterface
{
    /**
     * Подключаем трейт разрешения вложенных определений.
     */
    use ResolveSubDefinitionTrait;

    /** @var string имя класса */
    public $className;
    /** @var array аргументы конструктора */
    public $constructorArgs;
    /** @var callable колбек после инициализации */
    protected $callback;
    /** @var bool в единственном ли экземпляре */
    public $once = false;
    /** @var object|null экзепляр, если нужно единственный */
    public $onceInstance;
    /** @var array значения устанавливаемых свойств */
    public $properties = [];

    /**
     * Конструктор.
     * @param string|DefinitionInterface имя класса
     * @param array|null аргументы конструктора
     * @param callable|null колбек для вызова после инициализации объекта
     */
    public function __construct($className, array $args = null, callable $callback = null)
    {
        if (!is_string($className) && !($className instanceof DefinitionInterface)) {
            $def = DefinitionInterface::class;
            $type = PhpHelper::getType($className);
            throw new DiException("Argument 1 \$className must be a string or $def, $type given");
        }
        $this->className = $className;
        $this->constructorArgs = $args ?? [];
        $this->callback = $callback;
    }

    /**
     * Установка свойства класса.
     * @param string имя свойства
     * @param mixed значение свойства
     * @return self
     */
    public function property(string $name, $value): CreateObject
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Установка единственного экземпляра.
     * @param bool в единственном ли экземпляре
     * @return self
     */
    public function once(bool $once = true): CreateObject
    {
        $this->once = $once;
        return $this;
    }

    /**
     * Установка колбека после инициализации.
     * @param callable коллбек
     * @return self
     */
    public function callback(callable $callback): CreateObject
    {
        $this->callback = &$callback;
        return $this;
    }

    /**
     * Разрешение имени класса.
     * @param Container di контейнер
     * @return string имя класса
     */
    public function resolveClassName(Container &$c): string
    {
        return $this->resolveOrOriginal($this->className, $c);
    }

    /**
     * Разрешение коллбека после инициализации.
     * @param Container di контейнер
     * @return callable|null коллбек
     */
    public function resolveCallback(Container &$c): ?callable
    {
        return $this->resolveOrOriginal($this->callback, $c);
    }

    /**
     * Создание экземпляра объекта.
     * @param Container di контейнер
     * @param string имя класса
     * @return object экземпляр объекта
     */
    protected function createInstance(Container &$c): object
    {
        $constructorArgs = [];
        if ($this->constructorArgs) foreach ($this->constructorArgs as $name => &$value) {
            $constructorArgs[$name] = $this->resolveOrOriginal($value, $c);
        }
        $className = $this->resolveClassName($c);
        $object = new $className(...$constructorArgs);
        // if (!empty($this->properties)) foreach ($this->properties as $name => &$value) {
        //     $object->$name = $this->resolveOrOriginal($value, $c);
        // }
        $callback = $this->resolveCallback($c);
        if ($callback) $callback($object);
        return $object;
    }


    /**
     * Разрешение определения.
     * @param Container di контейнер
     * @return mixed результат
     * @throws DiException
     */
    public function resolve(Container &$c)
    {
        if (false === $this->canResolve($c)) {
            $className = $this->resolveClassName($c);
            throw new DiException("Class \"$className\" not found");
        }
        if (true === $this->once) {
            if (empty($this->onceInstance)) {
                $this->onceInstance = $this->createInstance($c);
            }
            return $this->onceInstance;
        } else {
            return $this->createInstance($c);
        }
    }

    /**
     * Проверка возможности разрешения определения.
     * @param Container di-контейнер
     * @return bool
     */
    public function canResolve(Container &$c): bool
    {
        $className = $this->resolveClassName($c);
        return class_exists($className, true);
    }
}

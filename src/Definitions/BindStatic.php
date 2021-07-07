<?php
/**
 * Определение установки статичексого класса.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\Definitions\Traits\ResolveSubDefinitionTrait;
use Evas\Di\DiException;

class BindStatic implements DefinitionInterface
{
    /**
     * Подключаем трейт разрешения вложенных определений.
     */
    use ResolveSubDefinitionTrait;

    /** @var string имя класса */
    public $className;
    /** @var callable коллбек */
    public $callback;
    /** @var bool были ли разрешены свойства класса */
    protected $resolved = false;

    /**
     * Конструктор.
     * @param string имя класса
     * @param callable ленивый коллбек для вызова статического класса
     */
    public function __construct(string $className, callable $callback = null)
    {
        $this->className = $className;
        $this->callback = &$callback;
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
     * Разрешение свойств класса.
     * @param Container di контейнер
     */
    public function resolveProps(Container &$c)
    {
        if (false === $this->resolved) {
            if ($this->callback) {
                $callback = $this->callback;
                $callback->bindTo($c);
                $callback();
            }
            $this->resolved = true;
        }
    }

    /**
     * Разрешение определения.
     * @param Container di контейнер
     * @return mixed результат
     * @throws DiException
     */
    public function resolve(Container &$c)
    {
        $className = $this->resolveClassName($c);
        $this->resolveProps($c);
        return $className;
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

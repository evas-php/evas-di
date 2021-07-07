<?php
/**
 * Di контейнер.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di;

use Evas\Base\App;
use Evas\Base\Help\PhpHelp;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\DiException;

include_once __DIR__ . '/functions.php';

class Container
{
    /** @var array маппинг определений */
    protected $definitions = [];

    /**
     * Инициализация нового контейнера.
     * @param string|array|null маппинг свойств или путь к файлу свойств
     * @return static
     */
    public static function init($arg = null): Container
    {
        return new static($arg);
    }

    /**
     * Конструктор.
     * Делайте инициализацию через Container::init(), 
     * чтобы подключить вспомогательные функции functions.php
     * @param string|array|null маппинг свойств или путь к файлу свойств
     */
    public function __construct($arg = null)
    {
        if (is_string($arg)) {
            $this->loadDefinitions($arg);
        } else if (is_array($arg)) {
            $this->setSome($arg);
        }
    }

    /**
     * Загрузка свойств из файла.
     * @param string путь к файлу
     * @return self
     * @throws DiException
     */
    public function loadDefinitions(string $path): Container
    {
        $path = App::resolveByApp($path);
        if (!is_readable($path)) {
            throw new DiException("Can not load definitions config file \"$path\"");
        }
        $definitions = include $path;
        if (PhpHelp::isNotAssoc($definitions)) {
            throw new DiException(sprintf('Incorrect definitions config file "%s", expected assoc array, %s given', $path, PhpHelp::getType($definitions, true)));
        }
        return $this->setSome($definitions);
    }

    /**
     * Устанока нескольких свойств в контейнер.
     * @param array маппинг свойств
     * @return self
     */
    public function setSome(array $definitions): Container
    {
        foreach ($definitions as $name => &$definition) {
            $this->set($name, $definition);
        }
        return $this;
    }

    /**
     * Установка свойства в контейнер.
     * @param string имя свойства
     * @param mixed значение
     * @return self
     */
    public function set(string $name, $value): Container
    {
        if ($value instanceof \Closure) {
            $value = $value->bindTo($this);
        }
        $this->definitions[$name] = $value;
        return $this;
    }

    /**
     * Удаление свойства контейнера.
     * @param string имя свойства
     * @return self
     */
    public function unset(string $name): Container
    {
        unset($this->definitions[$name]);
        return $this;
    }

    /**
     * Удаление нескольких свойств контейнера.
     * @param array массив имен свойств
     * @return self
     */
    public function unsetSome(array $names): Container
    {
        foreach ($names as &$name) {
            $this->unset($name);
        }
        return $this;
    }

    /**
     * Проверка наличия свойства в контейнере.
     * @param string имя свойства
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->definitions[$name]) ? true : false;
    }

    /**
     * Возврат оригинального значения или решения, если это определение.
     * @param string имя свойства
     * @param mixed значение
     * @return mixed оригинальное или решённое значение
     * @throws DiException
     */
    protected function resolveOrOriginal(string $name, $value)
    {
        try {
            return $value instanceof DefinitionInterface
            ? $value->resolve($this) : $value;
        } catch (\Exception $e) {
            throw new DiException("Error with entry resolve for \"$name\": " . $e->getMessage());
        }
    }

    /**
     * Получение оригинального значения свойства контейнера.
     * @param string имя свойства
     * @return mixed значение свойства
     */
    public function getOriginal(string $name)
    {
        return $this->definitions[$name];
    }

    /**
     * Получение значения свойства контейнера.
     * @param string имя свойства
     * @param mixed|null значение свойства по умолчанию
     * @param bool|null установить значение по умолчанию, если свойство отсутствует
     * @return mixed значение свойства или значение по умолчанию
     * @throws DiException
     */
    public function get(string $name, $default = null, bool $setDefaultIfNotHas = false)
    {
        if (!$this->has($name)) {
            if (func_num_args() > 1) {
                if (true === $setDefaultIfNotHas) {
                    $this->set($name, $default);
                }
                return $this->resolveOrOriginal($name, $default);
            }
            return null;
            // throw new DiException("Not found entry for \"$name\"");
        }
        $value = $this->getOriginal($name);
        return $this->resolveOrOriginal($name, $value);
    }

    /**
     * Проверка оригинального свойства на вызываемость.
     * @param string имя свойства
     * @return bool
     */
    public function isCallable(string $name): bool
    {
        return $this->getOriginal($name) instanceof \Closure;
    }

    /**
     * Вызов свойства контейнера.
     * @param string имя свойства
     * @param array аргументы
     * @return mixed результат выполнения
     * @throws DiException
     */
    public function call(string $name, array $args = null)
    {
        $value = $this->get($name);
        if ($value instanceof \Closure) {
            return call_user_func_array($value, $args ?? []);
        }
        throw new DiException("Entry \"$name\" is not Closure");
    }

    /**
     * Создание объекта из свойства контейнера.
     * @param string имя свойства
     * @param array аргументы
     * @return object
     * @throws DiException
     */
    public function make(string $name, array $args = null): object
    {
        $value = $this->get($name);
        if (!class_exists($value, true)) {
            throw new DiException("Class \"$value\" not found");
        }
        return new $value(...$args);
    }

    /**
     * Получение списка ключей установленных определений.
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->definitions);
    }
}

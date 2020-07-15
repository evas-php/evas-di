<?php
/**
 * @package evas-php\evas-di
 */
namespace Evas\Di;

use \Exception;

/**
 * Di-контейнер трейт.
 * @author Egor Vasyakin <egor@evas-php.com>
 * @since 1.0
 */
trait ContainerTrait
{
    // методы

    /**
     * Установка параметра или параметров контейнера.
     * @param string|array|object имя параметра или массив/объект параметров
     * @param mixed|null значение параметра или null
     * @return self
     */
    public function set($name, $value = null)
    {
        assert(is_string($name) || is_array($name) || is_object($name));
        if (is_string($name)) {
            $this->$name = $value;
        } else if (is_array($name) || is_object($name)) {
            foreach ($name as $subname => $value) {
                $this->set($subname, $value);
            }
        }
        return $this;
    }

    /**
     * Удаление параметра или параметров контейнера.
     * @param string|array имя параметра или массив имен параметров
     * @return self
     */
    public function unset($name)
    {
        assert(is_string($name) || is_array($name));
        if (is_string($name)) {
            unset($this->$name);
        } else if (is_array($name)) {
            foreach ($name as &$subname) {
                unset($this->$subname);
            }
        }
        return $this;
    }

    /**
     * Проверка наличия параметра или параметров.
     * @param string|array имя параметра или массив имен парамтеров
     * @param mixed значение, если параметр найден
     * @param mixed значение, если параметр не найден
     * @return mixed
     */
    public function has($name, $defaultIfHas = true, $defaultIfNotHas = false)
    {
        assert(is_string($name) || is_array($name));
        if (is_string($name)) {
            return isset($this->$name) ? $defaultIfHas : $defaultIfNotHas;
        } else if (is_array($name)) {
            $vars = [];
            foreach ($name as $subname) {
                $vars[$subname] = $this->has($subname, $defaultIfHas, $defaultIfNotHas);
            }
            return $vars;
        }
    }

    /**
     * Получение параметра или массива параметров контейнера.
     * @param string|array|object имя параметра 
     *                            или массив имен параметров 
     *                            или объект имен со значениями по умолчанию
     * @param mixed|null значение по умолчанию
     * @return mixed|array of mixed
     */
    public function get($name, $default = null)
    {
        assert(is_string($name) || is_array($name) || is_object($name));
        if (is_string($name)) {
            return $this->has($name) ? $this->$name : $default;
        } else if (is_array($name)) {
            $vars = [];
            foreach ($name as &$subname) {
                $vars[$subname] = $this->get($subname, $default);
            }
            return $vars;
        } else if (is_object($name)) {
            $vars = [];
            foreach ($name as $subname => $default) {
                $vars[$subname] = $this->get($subname, $default);
            }
            return $vars;
        }
    }

    /**
     * Вызов параметра-метода контейнера.
     * @param string имя параметра
     * @param array массив аргументов
     * @return mixed результат выполнения метода
     */
    public function call(string $name, array $arguments = [])
    {
        $var = $this->__call($name, $arguments);
    }



    // магия

    /**
     * Конструктор.
     * @var array|null своства
     */
    public function __construct(array $vars = null)
    {
        if ($vars) {
            $this->set($vars);
        }
    }

    /**
     * Магическая установка свойства.
     * @param string имя свойства
     * @param mixed значение
     */
    public function __set(string $name, $value)
    {
        if (is_callable($value)) {
            $value = $value->bindTo($this);
        }
        $this->$name = $value;
    }

    /**
     * Магическое получение свойства.
     * @param string имя свойства
     * @return null
     */
    public function __get(string $name)
    {
        return null;
    }

    /**
     * Магический вызов свойства, являющегося анонимной функцией.
     * @param string имя свойства
     * @param array|null аргументы
     */
    public function __call(string $name, array $arguments = [])
    {
        $var = $this->$name;
        if ($var instanceof \Closure) {
            return call_user_func_array($var, $arguments);
        }
    }
}

<?php
/**
* @package evas-php/evas-di
*/
namespace Evas\Di;

use \Exception;

/**
* Di-контейнер трейт.
* @author Egor Vasyakin <e.vasyakin@itevas.ru>
*/
trait ContainerTrait
{
    // методы

    public function set($name, $value = null)
    {
        if (is_string($name)) {
            $this->$name = $value;
        } else if (is_array($name) || is_object($name)) {
            foreach ($name as $subname => $value) {
                $this->set($subname, $value);
            }
        } else {
            throw new Exception('Argument 1 for set() must be string or array or object');
        }
    }

    // магия

    public function __construct(array $params = null)
    {
        if ($params) {
            $this->set($params);
        }
    }

    public function __set(string $name, $value)
    {
        if (is_callable($value)) {
            $value = $value->bindTo($this);
        }
        $this->$name = $value;
    }

    public function __get(string $name)
    {
        return null;
    }

    public function __call(string $name, array $arguments = [])
    {
        if (isset($this->$name)) {
            return call_user_func_array($this->$name, $arguments);
        }
    }
}

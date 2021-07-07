<?php
/**
 * Определение создание объекта свойства контейнера.
 * @package evas-php\evas-di
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Di\Definitions;

use \InvalidArgumentException;
use Evas\Base\Help\PhpHelp;
use Evas\Base\Exceptions\FileNotFoundException;
use Evas\Base\Traits\IncludeTrait;
use Evas\Di\Container;
use Evas\Di\Definitions\DefinitionInterface;
use Evas\Di\Definitions\Traits\ResolveSubDefinitionTrait;

class IncludeFile implements DefinitionInterface
{
    /**
     * Подключаем трейт загрузки файлов. 
     * Подключаем трейт разрешения вложенных определений.
     */
    use IncludeTrait;
    use ResolveSubDefinitionTrait;

    /** @var string|DefinitionInterface имя файла или определение */
    public $filename;
    /** @var array аргументы файла */
    public $fileargs;
    /** @var object контект файла */
    public $context;
    /** @var bool открывать ли единожды */
    public $once = false;
    /** @var object|null экзепляр, если нужно открывать единожды */
    public $onceInstance;

    /**
     * Конструктор.
     * @param string|DefinitionInterface имя файла
     * @param array|null аргументы файла
     * @param object|null контекст файла
     * @param bool|null открывать ли единожды
     * @throws InvalidArgumentException
     */
    public function __construct(
        $filename, array $args = null, 
        object &$context = null, bool $once = false
    ) {
        if (!is_string($filename) && !($filename instanceof DefinitionInterface)) {
            $def = DefinitionInterface::class;
            $type = PhpHelp::getType($filename);
            throw new InvalidArgumentException("Argument 1 \$filename must be a string or $def, $type given");
        }
        $this->filename = $filename;
        $this->fileargs = $args ?? [];
        $this->context = &$context;
        $this->once = $once;
    }

    /**
     * Установка открытия единожды.
     * @param bool открывать ли единожды
     * @return self
     */
    public function once(bool $once = true): IncludeFile
    {
        $this->once = $once;
        return $this;
    }

    /**
     * Разрешение имени файла.
     * @param Container di контейнер
     * @return string имя файла
     */
    public function resolveFilename(Container &$c): string
    {
        return $this->resolveOrOriginal($this->filename, $c);
    }

    /**
     * Открытие файла.
     * @param Container di контейнер
     * @return mixed возврат файла
     * @throws FileNotFoundException
     */
    protected function includeFile(Container &$c, string $filename)
    {
        $fileargs = [];
        foreach ($this->fileargs as &$value) {
            $fileargs[] = $this->resolveOrOriginal($value, $c);
        }
        $context = $this->resolveOrOriginal($this->context, $c);
        return $this->include($filename, $fileargs, $context);
    }

    /**
     * Разрешение определения.
     * @param Container di контейнер
     * @return mixed результат
     * @throws FileNotFoundException
     */
    public function resolve(Container &$c)
    {
        $filename = $this->resolveFilename($c);
        // if (false === $this->canResolve($c)) {
        //     throw new FileNotFoundException($filename);
        // }
        if (true === $this->once) {
            if (empty($this->onceInstance)) {
                $this->onceInstance = $this->includeFile($c, $filename);
            }
            return $this->onceInstance;
        } else {
            return $this->includeFile($c, $filename);
        }
    }

    /**
     * Проверка возможности разрешения определения.
     * @param Container di-контейнер
     * @return bool
     */
    public function canResolve(Container &$c): bool
    {
        $filename = $this->resolveFilename($c);
        return $this->canInclude($filename);
    }
}

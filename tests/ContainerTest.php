<?php
use Evas\Di\tests;

use Codeception\Util\Autoload;
use Evas\Di\Container;
use Evas\Di;

Autoload::addNamespace('Evas\\Di', 'vendor/evas-php/evas-di/src');

class ContainerTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        // Устанавливаем свойства $_SERVER
        if (empty($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }
        if (empty($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }
    }

    public function testLoadDefinitions()
    {
        // пустой конфиг di
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.empty.php');
        $this->assertCount(0, $di->getKeys());
        // нормальный конфиг di
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.php');
        $config = include __DIR__ .'/config/di.php';
        $this->assertCount(count($config), $di->getKeys());
        $this->assertEquals(array_keys($config), $di->getKeys());
    }

    public function testSetUnset()
    {
        $di = new Container;
        $di->set('author', 'Egor');
        $di->set('authorName', Di\call(function () {
            return $this->get('author');
        }));
        $this->assertCount(2, $di->getKeys());
        $this->assertTrue($di->has('author'));
        $this->assertTrue($di->has('authorName'));
        $this->assertEquals($di->get('author'), $di->get('authorName'));
        $di->unset('authorName');
        $di->unset('author');
        $this->assertFalse($di->has('author'));
        $this->assertFalse($di->has('authorName'));
        $this->assertCount(0, $di->getKeys());
        $this->assertEquals($di->get('author'), $di->get('authorName'));
    }

    public function testCreateObject()
    {
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.php');
        $request = $di->get('request');
        $this->assertTrue($request instanceof Evas\Web\WebRequest);
        $this->assertEquals('GET', $request->getMethod());
        $response = $di->get('response');
        $this->assertTrue($response instanceof Evas\Web\WebResponse);
    }

    public function testCreateObjectWithArgs()
    {
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.php');
        $controller = $di->get('controller');
        $this->assertTrue($controller instanceof Evas\Router\Controller);
        $this->assertTrue($controller->request instanceof Evas\Web\WebRequest);
        $this->assertEquals(
            spl_object_id($di->get('request')), 
            spl_object_id($controller->request)
        );
    }

    public function testCreateObjectOnce()
    {
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.php');
        $request = $di->get('request');
        $this->assertEquals(
            spl_object_id($request), 
            spl_object_id($di->get('request'))
        );
        $controller = $di->get('controller');
        $this->assertNotEquals(
            spl_object_id($controller), 
            spl_object_id($di->get('controller'))
        );
    }

    public function testIncludeFile()
    {
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.php');
        $exampleDbConfig = $di->get('exampleDbConfig');
        $this->assertTrue(is_array($exampleDbConfig));
        $this->assertEquals('root', $exampleDbConfig['username']);
    }

    public function testCall()
    {
        $di = (new Container)->loadDefinitions(__DIR__ .'/config/di.php');

        // функция в качестве свойства
        $authorNameCallback = $di->get('authorNameCallback');
        $this->assertTrue(is_callable($authorNameCallback));
        $this->assertEquals('Egor', $authorNameCallback());

        // функция обернутая в вызов при её получении
        $this->assertEquals('Egor', $di->get('authorName'));

        // функция обернутая в вызов при её получении
        // с использованием контекста внутри
        $this->assertEquals('Egor', $di->get('authorNameWithDiContext'));
    }
}

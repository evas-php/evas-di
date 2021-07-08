<?php
/**
 * Конфиг Di для тестов.
 */
use Evas\Di;

use Evas\Web\WebRequest;
use Evas\Web\WebResponse;
use Evas\Router\Controller;

return [
    'request' => Di\createOnce(WebRequest::class),
    'response' => Di\createOnce(WebResponse::class),
    'controller' => Di\create(Controller::class, [
        Di\get('request')
    ]),

    'exampleDbConfig' => Di\includeFileOnce(__DIR__ . '/db.example.php'),
    
    'author' => 'Egor',

    'authorNameCallback' => function () { return 'Egor'; },
    'authorName' => Di\call(function () { return 'Egor'; }),
    'authorNameWithDiContext' => Di\call(function () {
        return $this->get('author');
    }),

];

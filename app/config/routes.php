<?php
use Phalcon\Mvc\Router;


$router = new Router();

$router->add(
    '/',
    [
        'controller' => 'index',
        'action'     => 'index',
    ]
);

$router->add(
    '/tokencheck',
    [
        'controller' => 'index',
        'action'     => 'tokencheck',
    ]
);

$router->handle();


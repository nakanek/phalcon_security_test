<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.volt' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ));

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($config) {
    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    return new $class($dbConfig);
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

$di->setShared('logger', function() use ($config) {
    $formatter = new \Phalcon\Logger\Formatter\Line('%date% %type%  %message%');
    $logger = new \Phalcon\Logger\Adapter\File('../phalcon.log');
    $logger->setLogLevel(\Phalcon\Logger::DEBUG);
    $logger->setFormatter($formatter);
    return $logger;
});



/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $session = new Phalcon\Session\Adapter\Libmemcached(array(
        'servers' => array(
            array('host' => 'localhost', 'port' => 11211, 'weight' => 1),
        ),
        'client' => array(
            Memcached::OPT_HASH => Memcached::HASH_MD5,
            Memcached::OPT_PREFIX_KEY => 'prefix.',
        ),
       'lifetime' => 3600,
       'prefix' => 'my_'
    ));
    $session->start();

    return $session;
});

$di->set('security', function() {
    $security = new \Phalcon\Security();
    $security->setWorkFactor(12);
    return $security;
}, true);

$di->set('router', function() {
    require __DIR__ . '/routes.php';
    return $router;
});


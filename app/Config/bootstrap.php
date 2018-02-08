<?php

require ROOT . '/vendor/autoload.php';

$loader = new \Aura\Autoload\Loader;
$loader->addPrefix('MVCApp', APP_DIR);
$loader->register();

class_alias('\Respect\Validation\Validator', '\v');

$routes = require 'routes.php';
$config = require 'config.php';

$dispatcher = FastRoute\simpleDispatcher($routes);

$di = new \Pimple\Container();

$di['config'] = function() use ($config) {
    return $config;
};

$di['db'] = function() use ($config) {
    return new Medoo\Medoo($config['db']);
};

$di['twig'] = function() {
    $loader = new Twig_Loader_Filesystem(APP_DIR . '/View');
    return new Twig_Environment($loader);
};

unset($routes, $config);

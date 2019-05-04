<?php

use Slim\App;
use kaz29\Phai\Phai;

return function (App $app) {
    $container = $app->getContainer();

    $container['callableResolver'] = function ($container) {
        return new \Bnf\Slim3Psr15\CallableResolver($container);
    };

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // phai
    $container['phai'] = function ($c) {
        $settings = $c->get('settings')['phai'];
        $phai = Phai::initialize(Phai::createClient(), $settings['key'], null, null);
        return $phai;
    };
};

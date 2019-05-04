<?php

use Slim\App;
use kaz29\Phai\Middleware\ApplicationInsightsMiddleware;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);

    $app->add(new ApplicationInsightsMiddleware($app->getContainer()->get('phai')));
};

<?php declare(strict_types=1);

return static function(FastRoute\RouteCollector $routeCollector) {
    $routeCollector->addRoute(
        'GET',
        '/',
        [\Movary\HttpController\MovieHistoryController::class, 'fetchHistory']
    );
};

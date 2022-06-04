<?php declare(strict_types=1);

return static function(FastRoute\RouteCollector $routeCollector) {
    ### Frontend
    $routeCollector->addRoute(
        'GET',
        '/',
        [\Movary\HttpController\DashboardController::class, 'render']
    );
    $routeCollector->addRoute(
        'GET',
        '/settings',
        [\Movary\HttpController\SettingsController::class, 'render']
    );
    $routeCollector->addRoute(
        'GET',
        '/history',
        [\Movary\HttpController\HistoryController::class, 'render']
    );
    $routeCollector->addRoute(
        'POST',
        '/letterboxd-rating',
        [\Movary\HttpController\Letterboxd::class, 'uploadRatingCsv']
    );
    $routeCollector->addRoute(
        'POST',
        '/refresh-trakt',
        [\Movary\HttpController\SyncTraktController::class, 'execute']
    );
    $routeCollector->addRoute(
        'POST',
        '/refresh-tmdb',
        [\Movary\HttpController\SyncTmdbController::class, 'execute']
    );
    $routeCollector->addRoute(
        'POST',
        '/login',
        [\Movary\HttpController\AuthenticationController::class, 'login']
    );
    $routeCollector->addRoute(
        'GET',
        '/login',
        [\Movary\HttpController\AuthenticationController::class, 'renderLoginPage']
    );
    $routeCollector->addRoute(
        'GET',
        '/logout',
        [\Movary\HttpController\AuthenticationController::class, 'logout']
    );
    $routeCollector->addRoute(
        'GET',
        '/mostWatchedActors',
        [\Movary\HttpController\MostWatchedActorsController::class, 'renderPage']
    );
    $routeCollector->addRoute(
        'GET',
        '/mostWatchedDirectors',
        [\Movary\HttpController\MostWatchedDirectorsController::class, 'renderPage']
    );
    $routeCollector->addRoute(
        'GET',
        '/movie/{id:\d+}',
        [\Movary\HttpController\MovieController::class, 'renderPage']
    );
    $routeCollector->addRoute(
        'GET',
        '/person/{id:\d+}',
        [\Movary\HttpController\PersonController::class, 'renderPage']
    );
};

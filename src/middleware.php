<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// REST headers
$app->add(function ($request, $response, $next) {
    return $next($request, $response)
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// enriching request functionality
$app->add(function ($request, $response, $next) {
    return $next(App\Request::fromRequest($request), $response);
});

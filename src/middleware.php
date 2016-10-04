<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

$app->add(function ($request, $response, $next) {
    return $next($request, $response)
        ->withHeader('Access-Control-Allow-Origin', '*');
});

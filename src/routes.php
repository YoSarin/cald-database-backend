<?php
// Routes
$devController = new controller\Developer($container);

$app->get('/i/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
$app->get('/developer/install', array($devController, "install"));

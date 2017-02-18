<?php
use Slim\Http\Request as Request;
use JsonHelpers\Renderer as JsonRenderer;
use App\Context;
use App\ErrorHandler;

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

$loader = require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

$settings = require __DIR__ . '/../src/settings.php';
$settings['settings']['displayErrorDetails'] = false;
$settings['addContentLengthHeader'] = false;

if (file_exists(__DIR__ . '/../src/settings.local.php')) {
    $localSettings = require __DIR__ . '/../src/settings.local.php';
    $settings['settings'] = array_merge($settings['settings'], $localSettings['settings']);
}

$app = new \Slim\App($settings);

\App\Context::setApp($app);

$app->getContainer()['db'] = new \medoo($app->getContainer()->get('settings')['db']);

$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2'];
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));

// register the json response and error handlers
$jsonHelpers = new JsonHelpers\JsonHelpers($app->getContainer());
$jsonHelpers->registerResponseView();
$jsonHelpers->registerErrorHandlers();

$app->getContainer()['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return (new ErrorHandler($c))->handle($request, $response, $exception);
    };
};

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();

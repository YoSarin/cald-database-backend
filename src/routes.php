<?php
// Routes

$auth = new App\Auth($container);

$devController = new App\Controller\Developer($container);
$userController = new App\Controller\User($container);

$app->get('/developer/install', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "install"]));
$app->get('/developer/drop', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "drop"]));

$app->post('/user', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "newUser"]));
$app->post('/user/login', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "login"]));
$app->get('/user/login/check', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "check"]));

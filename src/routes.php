<?php
// Routes

$auth = new App\Auth($container);

$devController = new App\Controller\Developer($container);
$userController = new App\Controller\User($container);
$testController = new App\Controller\Test($container);

$app->post('/developer/create', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "create"]));
$app->post('/developer/drop', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "drop"]));

$app->post('/user', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "newUser"]));
$app->post('/user/login', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "login"]));
$app->get('/user/login/check', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "check"]));
$app->post('/user/verify/{hash}', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "verify"]));

$app->post('/test', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$testController, "test"]));
$app->post('/test/t/{team_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$testController, "team"]));
$app->post('/test/hs/{highschool_id}', $auth->verify(App\Auth\Check::ALLOW_HIGHSCHOOL_VIEW, [$testController, "hs"]));
